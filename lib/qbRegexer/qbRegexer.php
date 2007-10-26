<?php

class qbRegexer extends qbModule {

	private $text;
	private $regex;
	private $result;

	public function __construct($text = '', $regex = null) {
		$this->setModuleInfo(array(
			'author' => 'Tim \'Scytale\' Weber <scy-proj-qb@scytale.de>',
			'desc'   => 'Applies a list of regular expressions to an input string.',
		);
		$this->setText($text);
		$this->setRegex($regex);
	}

	public function forget() {
		$this->result = null;
	}

	public function getRegex() {
		return ($this->regex);
	}

	public function getResult() {
		return (($this->haveResult())?
		        ($this->result):
		        ($this->run()));
	}

	public function getText() {
		return ($this->text);
	}

	public function haveResult() {
		return (is_string($this->result));
	}

	public function run() {
		$r = $this->getText();
		// TODO: Continue here.
		$this->result = $r;
		return ($this->getResult());
	}

	public function setRegex($regex) {
		if ($regex === null)
			$regex = array();
		assert(is_array($regex));
		$this->forget();
		$this->regex = $regex;
	}

	public function setText($text) {
		assert(is_string($text));
		$this->forget();
		$this->text = $text;
	}

}

?>
