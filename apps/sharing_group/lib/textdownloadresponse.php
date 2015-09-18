<?php

namespace OCA\Sharing_Group;

use OCP\AppFramework\Http\DownloadResponse;

class TextDownloadResponse extends DownloadResponse {

	private $content;

	public function __construct($content, $filename, $contentType){
		parent::__construct($filename, $contentType);
		$this->content = $content;
	}

	public function render(){
		return $this->content;
	}
}
