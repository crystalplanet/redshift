Feature: Channels
  In order to communicate
  Tasks should be able to
  pass messages via channels

  Scenario: Block when trying to write to a channel that no process reads from
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
          $channel = new Channel();

          yield $channel->write('message');
      });
      """
    When I run the script
    And I kill the process
    Then the output should be empty

  Scenario: Block when trying to read from an empty channel
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
          $channel = new Channel();

          $message = yield $channel->read();
      });
      """
    When I run the script
    And I kill the process
    Then the output should be empty
