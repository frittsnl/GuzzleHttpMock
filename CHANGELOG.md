# v1.3.0
* FIX: Updated to php 7.1
* ADD: Option for responding to a RequestInterface via closure
* FIX: Tests for `zeroOrMoreTimes`

# v1.2.0

* MOD: Support newer Guzzle versions

# v1.1.5

* MOD: Add `role: Maintainer` to composer.json 

# v1.1.4

* MOD: Add Nick Shipes as a maintainer

# v1.1.3

* ADD: `zeroOrMoreTimes` expectation
* MOD: Improve error messages on no matching request expectation
* FIX: Check for null response body, before attempting to parse

# v1.1.2

* FIX: minor error handling fix

# v1.1.1

* FIX: Throw CompoundException on request with multiple failures

# v1.1.0

* ADD: Accept custom callables for all expectations
* ADD: Add custom expectation callables: `Any`, `ArrayContains`, `ArrayEquals`, `Equals`, `Matches`
* MOD: Refactor `RequestExpectation` to use callables for all request validations

# v1.0.2

* FIX: Was not properly comparison request bodies containing null data values.

# v1.0.1

* FIX: Fix incorrect 'call count' exception message

See git history for a full list of changes

# v1.0.0 (Initial Release)

* ADD: Initial project setup
* ADD: GuzzleHttpMock\Mock
* ADD: RequestExpectations:
    withUrl
    withMethod
    withQuery
    withQueryParams
    withJsonContentType
    withBody
    withBodyParams
    withJsonBodyParams
    once
    times
* ADD: Mock response methods:
    andResponseWith
    andResponseWithContent
    andRespondWithJson
* ADD: Documentation (README.md)
