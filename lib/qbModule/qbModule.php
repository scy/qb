<?php

abstract class qbModule {

	private $moduleAuthor      = null;
	private $moduleDescription = null;
	private $moduleURL         = null;
	private $moduleVersion     = null;

	public function getModuleAuthor() {
		return ($this->moduleAuthor);
	}

	public function getModuleName() {
		return (get_class($this));
	}

	public function getModuleDescription() {
		return ($this->moduleDescription);
	}

	public function getModuleURL() {
		return ($this->moduleURL);
	}

	public function getModuleVersion() {
		return ($this->moduleVersion);
	}

	protected function setModuleAuthor($author) {
		assert(is_string($author));
		return ($this->moduleAuthor = $author);
	}

	protected function setModuleDescription($desc) {
		assert(is_string($desc));
		return ($this->moduleDescription = $desc);
	}

	protected function setModuleURL($url) {
		assert(is_string($url));
		return ($this->moduleURL = $url);
	}

	protected function setModuleVersion($ver) {
		assert(is_int($ver));
		return ($this->moduleVersion = $ver);
	}

}

?>
