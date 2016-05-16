Feature: Dropping buffer
  In order to complete writes when full
  DroppingBuffer should be able to
  drop the items that are being written

  Scenario:
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;
      use CrystalPlanet\Redshift\Buffer\DroppingBuffer;

      Redshift::run(function () {
        $channel = new Channel(new DroppingBuffer(2));

        yield $channel->write(1);
        yield $channel->write(2);
        yield $channel->write(3);

        echo yield $channel->read();
        echo yield $channel->read();
      });
      """
    When I run the script
    Then the output should be:
    """
    12
    """
