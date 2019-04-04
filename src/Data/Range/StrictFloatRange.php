<?php
declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */

namespace ILIAS\Data\Range;

use ILIAS\Refinery\Validation\Constraints\ConstraintViolationException;

class StrictFloatRange
{
	/**
	 * @var FloatRange
	 */
	private $range;

	/**
	 * @param $minimum
	 * @param $maximum
	 * @throws ConstraintViolationException
	 */
	public function __construct($minimum, $maximum)
	{
		if ($maximum === $minimum) {
			throw new ConstraintViolationException(
				sprintf('The maximum("%s") and minimum("%s") can NOT be the same', $maximum, $minimum),
				'exception_maximum_minimum_same',
				array($maximum, $minimum)
			);
		}

		$this->range = new FloatRange($minimum, $maximum);
	}

	/**
	 * @param float $numberToCheck
	 * @return bool
	 */
	public function spans(float $numberToCheck) : bool
	{
		if ($numberToCheck <= $this->range->minimum()) {
			return false;
		} elseif ($numberToCheck >= $this->range->maximum()) {
			return false;
		}

		return true;
	}

	/**
	 * @return float
	 */
	public function minimum() : float
	{
		return $this->range->minimum();
	}

	/**
	 * @return float
	 */
	public function maximum() : float
	{
		return $this->range->maximum();
	}
}
