<?php

use Behat\Behat\Context\Context;
use Behat\MinkExtension\Context\MinkContext;

class VideoContext extends MinkContext implements Context
{
    /**
     * @AfterScenario
     */
    public function saveVideo($event)
    {
        $scenarioName = preg_replace('/[^a-zA-Z0-9]/', '_', $event->getScenario()->getTitle());
        $this->getSession()->getDriver()->saveVideo("$scenarioName.webm");
    }
}