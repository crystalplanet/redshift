Feature: Timeout
  In order to postpone execution
  I should be able to
  use timeout channels

  Scenario: Pausing the execution for at least 2 seconds
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;
      use CrystalPlanet\Redshift\Time\Time;

      Redshift::run(function () {
        $timeout = Time::after(2000);

        echo 'Hello';

        yield $timeout->read();

        echo ' World!';
      });
      """
    When I run the script
    And I wait for 1 second
    Then the output should be:
    """
    Hello
    """
    When I wait for the process to complete
    Then the output should be:
    """
    Hello World!
    """
    And the process should have ran for 2 seconds
