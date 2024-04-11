# Embargo Inheritance (`embargo_inheritance`)

Facilitate inheritance of embargo effects to child entities.

## Known Issues/Potential Issues/Risks

- It is unknown if more should/needs to be done to trigger indexing on hierarchy mutations
  - We have other behaviors in play that cause reindexing; however, if they were reworked/optimized, it might stop happening.
