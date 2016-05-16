Feature: Channels
  In order to communicate
  Tasks should be able to
  pass messages via channels

  Scenario: Processes should be able to pass messages through channels
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
        $channel = new Channel();

        async(function ($channel) {
          echo yield $channel->read();
        }, $channel);

        yield $channel->write('Hello World!');
      });
      """
    When I run the script
    And I wait for the process to complete
    Then the output should be:
      """
      Hello World!
      """

  Scenario: The processes should synchronize on reads and writes to the same channel
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
        $channel = new Channel();

        async(function ($channel) {
          for ($i = 0; $i<5; ++$i) {
            echo 'write: ' . $i . PHP_EOL;

            yield $channel->write(true);
          }
        }, $channel);

        for ($i = 0; $i<5; ++$i) {
          yield $channel->read();

          echo 'read: ' . $i . PHP_EOL;
        }
      });
      """
    When I run the script
    And I wait for the process to complete
    Then the output should be:
      """
      write: 0
      write: 1
      read: 0
      read: 1
      write: 2
      write: 3
      read: 2
      read: 3
      write: 4
      read: 4

      """

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

          echo 'Unreachable';
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

          echo 'Unreachable';
      });
      """
    When I run the script
    And I kill the process
    Then the output should be empty

  Scenario: The processes should not be blocked by asynchronous puts
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
          $channel = new Channel();

          $channel->put('Hello World!');

          echo yield $channel->read();
      });
      """
    When I run the script
    And I wait for the process to complete
    Then the output should be:
      """
      Hello World!
      """

  Scenario: The processes should not be blocked by asynchronous takes
    Given a script with:
      """
      <?php

      require_once 'vendor/autoload.php';

      use CrystalPlanet\Redshift\Redshift;
      use CrystalPlanet\Redshift\Channel\Channel;

      Redshift::run(function () {
          $channel = new Channel();

          $channel->take();

          yield $channel->write('true');

          echo 'Hello World!';
      });
      """
    When I run the script
    And I wait for the process to complete
    Then the output should be:
      """
      Hello World!
      """
