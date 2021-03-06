<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Edit request
 *
 * @author killing@leifos.de
 */
class ilLMEditRequest
{
    /**
     * Constructor
     */
    public function __construct(array $query_params)
    {
        $this->requested_ref_id = (int) $query_params["ref_id"];
    }

    /**
     * @return int
     */
    public function getRequestedRefId(): int
    {
        return $this->requested_ref_id;
    }
}