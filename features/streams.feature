Feature: Streams
  In order to perform async IO
  Tasks should be able to
  use streams

  Scenario: Processes should be able to read messages from streams
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Stream\Stream;

      Redshift::run(function () {
        $stream = new Stream(fopen('php://stdin', 'r'));

        echo yield $stream->read();
      });
      """
    When I write "Hello World!" to stdin
    And I run the script
    And I wait for the process to complete
    Then the output should be:
      """
      Hello World!
      """

  Scenario: Reads should block if there is no data ready on the stream
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Stream\Stream;

      Redshift::run(function () {
        $stream = new Stream(fopen('php://stdin', 'r'));

        echo yield $stream->read();

        echo "unreachable";
      });
      """
    When I run the script
    And I kill the process
    Then the output should be empty
