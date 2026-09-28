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

namespace pocketmine\level\generator\vanilla\network\mcpe\protocol;

use pocketmine\level\generator\vanilla\network\mcpe\NetworkSession;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EducationSettingsAgentCapabilities;
use pocketmine\level\generator\vanilla\network\mcpe\protocol\types\EducationSettingsExternalLinkSettings;

class EducationSettingsPacket extends DataPacket
{
	public const NETWORK_ID = ProtocolInfo::EDUCATION_SETTINGS_PACKET;

	public string $codeBuilderDefaultUri;
	public string $codeBuilderTitle;
	public bool $canResizeCodeBuilder;
	public bool $disableLegacyTitleBar;
	public string $postProcessFilter;
	public string $screenshotBorderResourcePath;
	public ?EducationSettingsAgentCapabilities $agentCapabilities;
	public ?string $codeBuilderOverrideUri;
	public bool $hasQuiz;
	public ?EducationSettingsExternalLinkSettings $linkSettings;

	public static function create(
		string $codeBuilderDefaultUri,
		string $codeBuilderTitle,
		bool $canResizeCodeBuilder,
		bool $disableLegacyTitleBar,
		string $postProcessFilter,
		string $screenshotBorderResourcePath,
		?EducationSettingsAgentCapabilities $agentCapabilities,
		?string $codeBuilderOverrideUri,
		bool $hasQuiz,
		?EducationSettingsExternalLinkSettings $linkSettings
	) : self {
		$result = new self();
		$result->codeBuilderDefaultUri = $codeBuilderDefaultUri;
		$result->codeBuilderTitle = $codeBuilderTitle;
		$result->canResizeCodeBuilder = $canResizeCodeBuilder;
		$result->disableLegacyTitleBar = $disableLegacyTitleBar;
		$result->postProcessFilter = $postProcessFilter;
		$result->screenshotBorderResourcePath = $screenshotBorderResourcePath;
		$result->agentCapabilities = $agentCapabilities;
		$result->codeBuilderOverrideUri = $codeBuilderOverrideUri;
		$result->hasQuiz = $hasQuiz;
		$result->linkSettings = $linkSettings;
		return $result;
	}

	public function getCodeBuilderDefaultUri() : string
	{
		return $this->codeBuilderDefaultUri;
	}

	public function getCodeBuilderTitle() : string
	{
		return $this->codeBuilderTitle;
	}

	public function canResizeCodeBuilder() : bool
	{
		return $this->canResizeCodeBuilder;
	}

	public function disableLegacyTitleBar() : bool
	{
		return $this->disableLegacyTitleBar;
	}

	public function getPostProcessFilter() : string
	{
		return $this->postProcessFilter;
	}

	public function getScreenshotBorderResourcePath() : string
	{
		return $this->screenshotBorderResourcePath;
	}

	public function getAgentCapabilities() : ?EducationSettingsAgentCapabilities
	{
		return $this->agentCapabilities;
	}

	public function getCodeBuilderOverrideUri() : ?string
	{
		return $this->codeBuilderOverrideUri;
	}

	public function getHasQuiz() : bool
	{
		return $this->hasQuiz;
	}

	public function getLinkSettings() : ?EducationSettingsExternalLinkSettings
	{
		return $this->linkSettings;
	}

	protected function decodePayload() : void
	{
		$this->codeBuilderDefaultUri = $this->getString();
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->codeBuilderTitle = $this->getString();
			$this->canResizeCodeBuilder = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->disableLegacyTitleBar = $this->getBool();
				$this->postProcessFilter = $this->getString();
				$this->screenshotBorderResourcePath = $this->getString();
				$this->agentCapabilities = $this->getBool() ? EducationSettingsAgentCapabilities::read($this) : null;
			}
			if ($this->getBool()) {
				$this->codeBuilderOverrideUri = $this->getString();
			} else {
				$this->codeBuilderOverrideUri = null;
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->hasQuiz = $this->getBool();
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->linkSettings = $this->getBool() ? EducationSettingsExternalLinkSettings::read($this) : null;
			}
		}
	}

	protected function encodePayload() : void
	{
		$this->putString($this->codeBuilderDefaultUri);
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putString($this->codeBuilderTitle);
			$this->putBool($this->canResizeCodeBuilder);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$this->putBool($this->disableLegacyTitleBar);
				$this->putString($this->postProcessFilter);
				$this->putString($this->screenshotBorderResourcePath);
				$agentCapabilities = $this->agentCapabilities;
				if ($agentCapabilities !== null) {
					$this->putBool(true);
					$agentCapabilities->write($this);
				} else {
					$this->putBool(false);
				}
			}
			$this->putBool($this->codeBuilderOverrideUri !== null);
			if ($this->codeBuilderOverrideUri !== null) {
				$this->putString($this->codeBuilderOverrideUri);
			}
		}
		if ($this->protocol >= ProtocolInfo::PROTOCOL_407) {
			$this->putBool($this->hasQuiz);
			if ($this->protocol >= ProtocolInfo::PROTOCOL_465) {
				$linkSettings = $this->linkSettings;
				if ($linkSettings !== null) {
					$this->putBool(true);
					$linkSettings->write($this);
				} else {
					$this->putBool(false);
				}
			}
		}
	}

	public function mustBeDecoded() : bool
	{
		return false;
	}

	public function handle(NetworkSession $session) : bool
	{
		return $session->handleEducationSettings($this);
	}
}
