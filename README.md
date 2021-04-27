<p align="center">
  <a href="https://hellofresh.com">
    <img width="120" src="https://www.hellofresh.de/images/hellofresh/press/HelloFresh_Logo.png">
  </a>
</p>

# hellofresh/engine

[![Build Status](https://travis-ci.org/hellofresh/engine.svg?branch=master)](https://travis-ci.org/hellofresh/engine)
[![Total Downloads](https://poser.pugx.org/hellofresh/engine/downloads)](https://packagist.org/packages/hellofresh/engine)

Welcome to HelloFresh Engine!!

Engine provides you all the capabilities to build an Event sourced application.

## Components

Engine is divided in a few small independent components.

* [CommandBus](src/CommandBus/README.md)
* [EventBus](src/EventBus/README.md)
* [EventDispatcher](src/EventDispatcher/README.md)
* [EventSourcing](src/EventSourcing/README.md)
* [EventStore](src/EventStore/README.md)
* [Serializer](src/Serializer/README.md)

## Install

```sh
composer require hellofresh/engine
```

## Usage

Here you can check a small tutorial of how to use this component in an orders scenario.

[Tutorial](docs/01-how_to.md)

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

