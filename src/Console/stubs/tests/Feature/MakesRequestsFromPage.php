<?php

namespace Tests\Feature;

trait MakesRequestsFromPage
{
    protected function fromPage($uri)
    {
        return $this->withServerVariables(['HTTP_REFERER' => $uri]);
    }
}
