# Embargo Inheritance (`embargo_inheritance`)

Facilitate inheritance of embargo effects to child entities.

## Installation

Install as
[usual](https://www.drupal.org/docs/extending-drupal/installing-modules).

### Requirements

- [`embargo`](https://github.com/discoverygarden/embargo)
- [`islandora_member_of_entailment`](https://github.com/discoverygarden/islandora_member_of_entailment/)


## Configuration

For general use, things should hook-in to automatically apply; however, some
additional configuration may be necessary for use with `search_api(_solr)`

### `search_api(_solr)` config

We have defined the `embargo_inheritance_join_processor` processor ("Embargo
inheritance, join-wise") to allow for the filtering of `search_api_solr` results
according to Islandora's concept of membership (`field_member_of`).

Similar to `embargo`'s `embargo_join_processor`, it requires the embargo
entities to be indexed into the same index as the relevant node/media/files
which are to be filtered.

## Known Issues/Potential Issues/Risks

- It is unknown if more should/needs to be done to trigger indexing on hierarchy mutations
  - We have other behaviors in play that cause reindexing; however, if they were reworked/optimized, it might stop happening.

## Troubleshooting/Issues

Having problems or solved a problem? Contact
[discoverygarden](http://www.discoverygarden.ca/).

## Maintainers/Sponsors

* [discoverygarden](http://www.discoverygarden.ca/)

## License
[GPLv3](http://www.gnu.org/licenses/gpl-3.0.txt)
