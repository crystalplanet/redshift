## Asynchronous Fibonacci

```php
use CrystalPlanet\Redshift\Redshift;
use CrystalPlanet\Redshift\Channel\Channel;

function fibonacci($c, $quit) {
    $x = 0;
    $y = 1;

    while (true) {
        list($value, $channel) = yield Channel::any([$c, $x], $quit);

        switch ($channel) {
            case $c:
                $tmp = $x + $y;
                $x = $y;
                $y = $tmp;
                break;

            case $quit:
                echo $value;
                return;
        }
    }
}

Redshift::run(function () {
    $c = new Channel();
    $quit = new Channel();

    async(function ($c, $quit) {
        for ($i = 0; $i < 10; ++$i) {
            $n = yield $c->read();
            echo "$n\n";
        }

        yield $quit->write("Quit\n");
    }, $c, $quit);

    yield fibonacci($c, $quit);
});
```

Outputs:

```
0
1
1
2
3
5
8
13
21
34
Quit
```

