<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\PHPProcess;

class FeatureContext implements SnippetAcceptingContext
{
    /**
     * @var PHPProcess
     */
    private $process;

    /**
     * @Given /^a script with:$/
     *
     * @param string $filename
     * @param PyStringNode $content
     */
    public function aScriptWith(PyStringNode $content)
    {
        $this->process = new PHPProcess((string) $content);
    }

    /**
     * @When /^I run the script$/
     */
    public function iRunTheScript()
    {
        $this->process->start();
    }

    /**
     * @When /^I kill the process$/
     */
    public function iKillTheProcess()
    {
        PHPUnit_Framework_Assert::assertTrue(
            $this->process->isRunning(),
            "The process already quit!"
        );

        $this->process->stop();
    }

    /**
     * @Then /^the output should be empty$/
     */
    public function theOutputShouldBeEmpty()
    {
        if ($this->process->isRunning()) {
            $this->process->wait();
        }

        PHPUnit_Framework_Assert::assertEquals("", $this->process->getOutput());
    }

    /**
     * @Then /^the output should be:$/
     */
    public function theOutputShouldBe(PyStringNode $content)
    {
        if ($this->process->isRunning()) {
            $this->process->wait();
        }

        PHPUnit_Framework_Assert::assertEquals((string) $content, $this->process->getOutput());
    }
}
