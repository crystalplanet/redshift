Feature: Sliding buffer
  In order to complete writes when full
  SlidingBuffer should be able to
  drop the oldest items in the buffer

  Scenario:
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;
      use CrystalPlanet\Redshift\Buffer\SlidingBuffer;

      Redshift::run(function () {
        $channel = new Channel(new SlidingBuffer(2));

        yield $channel->write(1);
        yield $channel->write(2);
        yield $channel->write(3);

        echo yield $channel->read();
        echo yield $channel->read();
      });
      """
    When I run the script
    And I wait for the process to complete
    Then the output should be:
    """
    23
    """
