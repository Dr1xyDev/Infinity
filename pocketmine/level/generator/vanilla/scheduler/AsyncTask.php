<?php

/*
 *
 *   _____       _                          _
 *  / ____|     | |                        (_)
 * | (___  _   _| |__  _ __ ___   __ _ _ __ _ _ __   ___
 *  \___ \| | | | '_ \| '_ ` _ \ / _` | '__| | '_ \ / _ \
 *  ____) | |_| | |_) | | | | | | (_| | |  | | | | |  __/
 * |_____/ \__,_|_.__/|_| |_| |_|\__,_|_|  |_|_| |_|\___|
 *
 * This program is private software. No license required.
 * Publication of this program is forbidden and will be punished.
 *
 * @author SEMENNEJO
 * @link vk.com/vk.snikers && t.me/semennejo
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\vanilla\scheduler;

use BadMethodCallException;
use InvalidStateException;
use pmmp\thread\Runnable;
use pmmp\thread\Thread as NativeThread;
use pmmp\thread\ThreadSafeArray;
use pocketmine\Server;
use pocketmine\level\generator\vanilla\thread\NonThreadSafeValue;
use pocketmine\level\generator\vanilla\timings\Timings;
use RuntimeException;
use Throwable;
use WeakMap;

use function is_null;
use function is_scalar;
use function serialize;
use function unserialize;

/**
 * Class used to run async tasks in other threads.
 *
 * An AsyncTask does not have its own thread. It is queued into an AsyncPool and executed if there is an async worker
 * with no AsyncTask running. Therefore, an AsyncTask SHOULD NOT execute for more than a few seconds. For tasks that
 * run for a long time or infinitely, start another thread instead.
 *
 * WARNING: Any non-Threaded objects WILL BE SERIALIZED when assigned to members of AsyncTasks or other Threaded object.
 * If later accessed from said Threaded object, you will be operating on a COPY OF THE OBJECT, NOT THE ORIGINAL OBJECT.
 * If you want to store non-serializable objects to access when the task completes, store them using
 * {@link AsyncTask::storeLocal}.
 *
 * WARNING: As of pthreads v3.1.6, arrays are converted to Volatile objects when assigned as members of Threaded objects.
 * Keep this in mind when using arrays stored as members of your AsyncTask.
 *
 * WARNING: Do not call PocketMine-MP API methods from other Threads!!
 */
abstract class AsyncTask extends Runnable
{
	/**
	 * @var WeakMap|null
	 * Used to store objects on the main thread which should not be serialized.
	 */
	private static ?WeakMap $localObjectStorage = null;

	/** @var AsyncWorker $worker */
	protected $worker = null;

	public ThreadSafeArray $progressUpdates;

	private $result = null;
	private $cancelRun = false;
	/** @var int|null */
	private $taskId = null;

	private $crashed = false;
	private $isGarbage = false;

	public function run() : void
	{
		$this->result = null;

		if (!$this->cancelRun) {
			try {
				$timings = Timings::getAsyncTaskRunTimings($this);
				$timings->startTiming();

				try {
					$this->onRun();
				} finally {
					$timings->stopTiming();
				}
			} catch (Throwable $e) {
				$this->crashed = true;

				\GlobalLogger::get()->logException($e);
			}
		}

		$this->setGarbage();
	}

	public function isCrashed() : bool
	{
		return $this->crashed || $this->isTerminated();
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		if ($this->result instanceof NonThreadSafeValue) {
			return $this->result->deserialize();
		}
		return $this->result;
	}

	public function cancelRun()
	{
		$this->cancelRun = true;
	}

	public function hasCancelledRun() : bool
	{
		return $this->cancelRun;
	}

	public function hasResult() : bool
	{
		return $this->result !== null;
	}

	/**
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = is_scalar($result) || is_null($result) ? $result : new NonThreadSafeValue($result);
	}

	public function getWorker() : ?AsyncWorker
	{
		return $this->worker;
	}

	public function setWorker(AsyncWorker $worker) : void
	{
		$this->worker = $worker;
	}

	public function setTaskId(int $taskId)
	{
		$this->taskId = $taskId;
	}

	/**
	 * @return int|null
	 */
	public function getTaskId()
	{
		return $this->taskId;
	}

	/**
	 * @see AsyncWorker::getFromThreadStore()
	 *
	 * @return mixed
	 */
	public function getFromThreadStore(string $identifier)
	{
		if ($this->worker === null || $this->isGarbage()) {
			throw new BadMethodCallException("Objects stored in AsyncWorker thread-local storage can only be retrieved during task execution");
		}
		return $this->worker->getFromThreadStore($identifier);
	}

	/**
	 * @see AsyncWorker::saveToThreadStore()
	 *
	 * @param mixed $value
	 */
	public function saveToThreadStore(string $identifier, $value)
	{
		if ($this->worker === null || $this->isGarbage()) {
			throw new BadMethodCallException("Objects can only be added to AsyncWorker thread-local storage during task execution");
		}
		$this->worker->saveToThreadStore($identifier, $value);
	}

	/**
	 * @see AsyncWorker::removeFromThreadStore()
	 */
	public function removeFromThreadStore(string $identifier) : void
	{
		if ($this->worker === null || $this->isGarbage()) {
			throw new BadMethodCallException("Objects can only be removed from AsyncWorker thread-local storage during task execution");
		}
		$this->worker->removeFromThreadStore($identifier);
	}

	/**
	 * Actions to execute when run
	 *
	 * @return void
	 */
	abstract public function onRun();

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 *
	 * @return void
	 */
	public function onCompletion(Server $server)
	{

	}

	/**
	 * Call this method from {@link AsyncTask::onRun} (AsyncTask execution thread) to schedule a call to
	 * {@link AsyncTask::onProgressUpdate} from the main thread with the given progress parameter.
	 *
	 * @param mixed $progress A value that can be safely serialize()'ed.
	 */
	public function publishProgress($progress)
	{
		$this->progressUpdates[] = serialize($progress);
	}

	/**
	 * @internal Only call from AsyncPool.php on the main thread
	 */
	public function checkProgressUpdates(Server $server)
	{
		while ($this->progressUpdates->count() !== 0) {
			$progress = $this->progressUpdates->shift();
			$this->onProgressUpdate($server, unserialize($progress));
		}
	}

	/**
	 * Called from the main thread after {@link AsyncTask::publishProgress} is called.
	 * All {@link AsyncTask::publishProgress} calls should result in {@link AsyncTask::onProgressUpdate} calls before
	 * {@link AsyncTask::onCompletion} is called.
	 *
	 * @param mixed $progress The parameter passed to {@link AsyncTask::publishProgress}. It is serialize()'ed
	 *                        and then unserialize()'ed, as if it has been cloned.
	 */
	public function onProgressUpdate(Server $server, $progress)
	{

	}

	/**
	 * Saves mixed data in thread-local storage on the parent thread. You may use this to retain references to objects
	 * or arrays which you need to access in {@link AsyncTask::onCompletion} which cannot be stored as a property of
	 * your task (due to them becoming serialized).
	 *
	 * Scalar types can be stored directly in class properties instead of using this storage.
	 *
	 * Objects stored in this storage MUST be retrieved through {@link AsyncTask::fetchLocal} when {@link AsyncTask::onCompletion} is called.
	 * Otherwise, a NOTICE level message will be raised and the reference will be removed after onCompletion exits.
	 *
	 * WARNING: THIS METHOD SHOULD ONLY BE CALLED FROM THE MAIN THREAD!
	 *
	 * @param mixed $complexData the data to store
	 *
	 * @throws BadMethodCallException if called from any thread except the main thread
	 */
	protected function storeLocal($complexData)
	{
		if ($this->worker !== null && $this->worker === NativeThread::getCurrentThread()) {
			throw new BadMethodCallException("Objects can only be stored from the parent thread");
		}

		if (self::$localObjectStorage === null) {
			self::$localObjectStorage = new WeakMap(); //lazy init
		}

		if (isset(self::$localObjectStorage[$this])) {
			throw new InvalidStateException("Already storing complex data for this async task");
		}
		self::$localObjectStorage[$this] = $complexData;
	}

	/**
	 * Returns and removes mixed data in thread-local storage on the parent thread. Call this method from
	 * {@link AsyncTask::onCompletion} to fetch the data stored in the object store, if any.
	 *
	 * If no data was stored in the local store, or if the data was already retrieved by a previous call to fetchLocal,
	 * do NOT call this method, or an exception will be thrown.
	 *
	 * Do not call this method from {@link AsyncTask::onProgressUpdate}, because this method deletes stored data, which
	 * means that you will not be able to retrieve it again afterwards. Use {@link AsyncTask::peekLocal} instead to
	 * retrieve stored data without removing it from the store.
	 *
	 * WARNING: THIS METHOD SHOULD ONLY BE CALLED FROM THE MAIN THREAD!
	 *
	 * @return mixed
	 *
	 * @throws RuntimeException if no data were stored by this AsyncTask instance.
	 * @throws BadMethodCallException if called from any thread except the main thread
	 */
	protected function fetchLocal()
	{
		try {
			return $this->peekLocal();
		} finally {
			if (self::$localObjectStorage !== null) {
				unset(self::$localObjectStorage[$this]);
			}
		}
	}

	/**
	 * Returns mixed data in thread-local storage on the parent thread **without clearing** it. Call this method from
	 * {@link AsyncTask::onProgressUpdate} to fetch the data stored if you need to be able to access the data later on,
	 * such as in another progress update.
	 *
	 * Use {@link AsyncTask::fetchLocal} instead from {@link AsyncTask::onCompletion}, because this method does not delete
	 * the data, and not clearing the data will result in a warning for memory leak after {@link AsyncTask::onCompletion}
	 * finished executing.
	 *
	 * WARNING: THIS METHOD SHOULD ONLY BE CALLED FROM THE MAIN THREAD!
	 *
	 * @return mixed
	 *
	 * @throws RuntimeException if no data were stored by this AsyncTask instance
	 * @throws BadMethodCallException if called from any thread except the main thread
	 */
	protected function peekLocal()
	{
		if ($this->worker !== null && $this->worker === NativeThread::getCurrentThread()) {
			throw new BadMethodCallException("Objects can only be retrieved from the parent thread");
		}

		if (self::$localObjectStorage === null || !isset(self::$localObjectStorage[$this])) {
			throw new InvalidStateException("No complex data stored for this async task");
		}

		return self::$localObjectStorage[$this];
	}

	/**
	 * @internal Called by the AsyncPool to destroy any leftover stored objects that this task failed to retrieve.
	 */
	public function removeDanglingStoredObjects() : bool
	{
		if (self::$localObjectStorage !== null && isset(self::$localObjectStorage[$this])) {
			unset(self::$localObjectStorage[$this]);
			return true;
		}

		return false;
	}

	public function isGarbage() : bool
	{
		return $this->isGarbage;
	}

	public function setGarbage() : void
	{
		$this->isGarbage = true;
	}
}
