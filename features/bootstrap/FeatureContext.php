<?php

use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Process\PhpProcess;

class FeatureContext implements SnippetAcceptingContext
{
    /**
     * @var PHPProcess
     */
    private $process;

    /**
     * @var int
     */
    private $start;

    /**
     * @Given /^a script with:$/
     *
     * @param string $filename
     * @param PyStringNode $content
     */
    public function aScriptWith(PyStringNode $content)
    {
        $this->process = new PhpProcess((string) $content);
    }

    /**
     * @When /^I run the script$/
     */
    public function iRunTheScript()
    {
        $this->process->start();
        $this->start = time();
    }

    /**
     * @When /^I kill the process$/
     */
    public function iKillTheProcess()
    {
        Assert::assertTrue(
            $this->process->isRunning(),
            "The process already quit!"
        );

        $this->process->stop();
    }

    /**
     * @When /^I wait for the process to complete$/
     */
    public function iWaitForTheProcessToComplete()
    {
        if ($this->process->isRunning()) {
            $this->process->wait();
        }
    }

    /**
     * @When /^I wait for (\d+) seconds?$/
     */
    public function iWaitForSecond($seconds)
    {
        sleep($seconds);
    }

    /**
     * @When /^I write "([^"]*)" to stdin$/
     */
    public function iWriteToStdin($input)
    {
        $this->process->setInput($input);
    }

    /**
     * @Then /^the output should be empty$/
     */
    public function theOutputShouldBeEmpty()
    {
        Assert::assertEquals(
            "",
            $this->process->getErrorOutput() . $this->process->getOutput()
        );
    }

    /**
     * @Then /^the output should be:$/
     */
    public function theOutputShouldBe(PyStringNode $content)
    {
        Assert::assertEquals(
            (string) $content,
            $this->process->getErrorOutput() . $this->process->getOutput()
        );
    }

    /**
     * @Then /^the process should have ran for (\d+) seconds?$/
     */
    public function theProcessShouldHaveRanForSeconds($seconds)
    {
        Assert::assertGreaterThanOrEqual(2, time() - $this->start);
    }
}
