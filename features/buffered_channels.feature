Feature: Buffered channels
  In order to prevent blocking
  Channels should be able to
  buffer the messages

  Scenario: Writes should not block if they can be buffered
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;
      use CrystalPlanet\Redshift\Buffer\Buffer;

      Redshift::run(function () {
        $channel = new Channel(new Buffer(1));

        yield $channel->write('Hello World!');

        echo yield $channel->read();
      });
      """
    When I run the script
    Then the output should be:
      """
      Hello World!
      """

  Scenario: Writes should block if the buffer is full
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;
      use CrystalPlanet\Redshift\Buffer\Buffer;

      Redshift::run(function () {
        $channel = new Channel(new Buffer(1));

        yield $channel->write(1);
        yield $channel->write(2);

        echo 'Unreachable';
      });
      """
    When I run the script
    And I kill the process
    Then the output should be empty
