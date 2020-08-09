<?php

namespace Translate\Parser;

/**
 * @link https://github.com/MAXakaWIZARD/PoParser
 */
class Entry {

	/**
	 * @var string
	 */
	protected $context;

	/**
	 * @var string|string[]
	 */
	protected $msgId;

	/**
	 * @var string|string[]|null
	 */
	protected $msgIdPlural;

	/**
	 * @var bool
	 */
	protected $fuzzy = false;

	/**
	 * @var bool
	 */
	protected $obsolete = false;

	/**
	 * @var bool
	 */
	protected $header = false;

	/**
	 * @var string[]
	 */
	protected $flags = [];

	/**
	 * @var array
	 */
	protected $translations = [];

	/**
	 * @var array
	 */
	protected $references = [];

	/**
	 * @var string
	 */
	protected $extractedComment;

	/**
	 * @var string
	 */
	protected $translatorComment;

	/**
	 * @param array $properties
	 */
	public function __construct(array $properties) {
		$this->context = $properties['msgctxt'];
		$this->translatorComment = $properties['tcomment'];
		$this->extractedComment = $properties['ccomment'];
		$this->msgId = $properties['msgid'];
		$this->msgIdPlural = isset($properties['msgid_plural']) ? $properties['msgid_plural'] : null;
		$this->fuzzy = $properties['fuzzy'] === true;
		$this->obsolete = $properties['obsolete'] === true;
		$this->header = $properties['header'] === true;
		$this->translations = $properties['msgstr'];
		$this->references = $properties['references'];
		$this->flags = $properties['flags'];
	}

	/**
	 * @return bool
	 */
	public function isHeader(): bool {
		return $this->header;
	}

	/**
	 * @return bool
	 */
	public function isFuzzy(): bool {
		return $this->fuzzy;
	}

	/**
	 * @return string
	 */
	public function getMsgId(): string {
		return is_array($this->msgId) ? implode('', $this->msgId) : (string)$this->msgId;
	}

	/**
	 * @return string|null
	 */
	public function getMsgIdPlural(): ?string {
		return is_array($this->msgIdPlural) ? implode('', $this->msgIdPlural) : $this->msgIdPlural;
	}

	/**
	 * @return bool
	 */
	public function isObsolete(): bool {
		return $this->obsolete;
	}

	/**
	 * @return array
	 */
	public function getTranslations(): array {
		return $this->translations;
	}

	/**
	 * @param int $index
	 *
	 * @return string
	 */
	public function getTranslation($index = 0): string {
		return (isset($this->translations[$index])) ? $this->translations[$index] : '';
	}

	/**
	 * @return bool
	 */
	public function isPlural(): bool {
		return !empty($this->msgIdPlural);
	}

	/**
	 * @param string $flag
	 *
	 * @return bool
	 */
	public function hasFlag($flag): bool {
		return in_array($flag, $this->flags, true);
	}

	/**
	 * @return string
	 */
	public function getContext(): string {
		return $this->context;
	}

	/**
	 * @return string
	 */
	public function getExtractedComment(): string {
		return $this->extractedComment;
	}

	/**
	 * @return string
	 */
	public function getTranslatorComment(): string {
		return $this->translatorComment;
	}

	/**
	 * @return string[]
	 */
	public function getFlags(): array {
		return $this->flags;
	}

	/**
	 * @return array
	 */
	public function getReferences(): array {
		return $this->references;
	}

}
