## redshift

[![Code Climate](https://codeclimate.com/github/crystalplanet/redshift/badges/gpa.svg)](https://codeclimate.com/github/crystalplanet/redshift) [![Test Coverage](https://codeclimate.com/github/crystalplanet/redshift/badges/coverage.svg)](https://codeclimate.com/github/crystalplanet/redshift/coverage)

A PHP library aimed at providing facilities for asynchronous programming and communication. Based on goroutines and core.async.

```
composer require crystalplanet/redshift
```

## What problem does it solve ?

PHP is great at serving web pages. But today we no longer talk about web pages but web applications, which are more complex than ever, and have to meet the ever growing expectations. To meet those expectations, a new ways of writing applications are necessary. Even though the language has drastically evolved, in most cases, it's still used as an oversophisticated template.

Because of it's lack of concurrency features, PHP needs a process manager like Apaches *mod_php* or *php-fpm* to run on the server. Therefor the application has no control over it's lifetime and keeping track of stuff between the requests becomes very complicated.

Redshift aims to provide way to achieve concurrency in PHP, making way for a new generation of 'pure' applications.

