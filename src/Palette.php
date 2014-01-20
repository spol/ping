<?php

namespace Spol\Ping;

class Palette
{
	private $entries = array();

	public function __construct($data)
	{
		$bytes = array_values(unpack('C*', $data));

		for ($i = 0; $i < count($bytes); $i+=3)
		{
			$color = new Color();
			//$color->setDepth(8);
			$color->setColor($bytes[$i], $bytes[$i+1], $bytes[$i+2]);

			$this->entries[] = $color;
		}
	}

	public function getEntry($index)
	{
		return isset($this->entries[$index]) ? $this->entries[$index] : null;
	}

	public function setEntryAlpha($index, $alpha)
	{
		$this->entries[$index]->alpha = $alpha;
	}

	public function getEntryCount()
	{
		return count($this->entries);
	}
}