# Bwilliamson/Exporter
Utilize as much existing functionality in Magento 2.4.4 and extend export options to custom destinations.
## Description
I'll get to this. tldr is fake filetypes that we use to move JSON into things like a rest client  
*This was built on Magento 2.4.4 community using [Mark Shusts' magento-docker development environment.](https://github.com/markshust/docker-magento)*

[comment]: <> (## Getting Started)

### Dependencies

* To be determined beyond Magento 2.4.x
* I avoided using PHP8 specific things to help with compatibility

### Installing

* Copy all contents to app/code/Bwilliamson/Exporter
* Run `bin/magento setup:upgrade && bin/magento setup:di:compile`

## Help
I'll try to help, but this is a work in progress.

## Authors
Ben Williamson, as himself. [github](github.com/bwilliamson55)

## Version History

* 0.1
    * Proof of concept - pushing adapter generated JSON to the log instead of a csv
      * *should* work with all OOB entity types

## License

This project is licensed under The Unlicense - see the LICENSE.md file for details.  
Basically, do as you wish.

## Acknowledgments

* [Mark Shusts' magento-docker development environment.](https://github.com/markshust/docker-magento)
* [SwiftOtter Training](https://swiftotter.com/)
