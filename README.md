Repositories manager and command executor
=========================================

Configuration reference
-----------------------

```yaml
config:
    # local path where the repositories should stored in
    storage: /local/path
    # (optional) A scheme for the repository directories, created in the storage path
    directory-scheme: %repository%

providers:
    # full bitbucket example
    bitbucket:
        # the remote name
        remote: origin
        # the provider type
        type: bitbucket
        # the name of the owner
        owner: contao-community-alliance
        # repository specific settings, all matching setting will be merged in order
        repositories:
            # a specific repository
            build-system-repositories:
                # (optional) select branches to work on
                branches:
                    # a specific branch
                    - "master"
                    # a simple wildcard branch pattern
                    - "dev-*"
                    # a regexp branch pattern (a regexp must start and end with the same non-numeric character)
                    - "~release/\d+\.\d+~"
                # (optional) select tags to work on
                tags:
                    # a specific version
                    - 1.2.3
                    # a simple wildcard version pattern
                    - 2.*
                    # a regexp version pattern (a regexp must start and end with the same non-numeric character)
                    - ~2\.\d+~
                # (optional)
                tag:
                    min: 1.1
                    max: 2
                    ignore: [ 1.2.2, 1.2.4 ]
            # a simple wildcard repository pattern
            build-system-*:
                ...
            # a regexp repository pattern (a regexp must start and end with the same non-numeric character)
            ~^build-system.*~:
                ...
        # (optional)
        tag:
            # (optional) sort tags in a specific order
            sorting: desc
            # (optional) how to compare tags with each other, use a (custom) comparing function here
            compareFunction: version_compare
            # (optional) if multiple tags are selected, limit to a specific amount (a value <=0 disable this function)
            limit: -1
        # authentication informations
        auth:
            # basic auth
            type: basic
            username: xxx
            password: xxx

    # full github example
    github:
        # the remote name
        remote: origin
        # the provider type
        type: github
        # the name of the owner
        owner: contao-community-alliance
        # repository specific settings, all matching setting will be merged in order
        repositories:
            # a specific repository
            build-system-repositories:
                # (optional) select branches to work on
                branches:
                    # a specific branch
                    - "master"
                    # a simple wildcard branch pattern
                    - "dev-*"
                    # a regexp branch pattern (a regexp must start and end with the same non-numeric character)
                    - "~release/\d+\.\d+~"
                # (optional) select tags to work on
                tags:
                    # a specific version
                    - 1.2.3
                    # a simple wildcard version pattern
                    - 2.*
                    # a regexp version pattern (a regexp must start and end with the same non-numeric character)
                    - ~2\.\d+~
                # (optional) min/max range
                tag:
                    min: 1.1
                    max: 2
            # a simple wildcard repository pattern
            build-system-*:
                ...
            # a regexp repository pattern (a regexp must start and end with the same non-numeric character)
            ~^build-system.*~:
                ...
        # (optional)
        tag:
            # (optional) sort tags in a specific order
            sorting: desc
            # (optional) how to compare tags with each other, use a (custom) comparing function here
            compareFunction: version_compare
            # (optional) if multiple tags are selected, limit to a specific amount (a value <=0 disable this function)
            limit: -1
        # authentication informations
        auth:
            # basic auth
            type: basic
            username: xxx
            password: xxx

    # full vcs example
    vcs:
        type: git
        remote: "<remote name>"
        owner: "<owner name>"
        name: "<repository name>"
        readUrl: "<repository read url>"
        writeUrl: "<repository write url>"
        webUrl: "<repository web url>"
        ref: "<ref spec>"
        realRef: "<real ref spec>"
        refType: "[ branch | tag ]"

# Actions that are executed before the execution is started, for syntax see the actions section
# Warning: repository specific placeholders are not available here!
pre:
    ...

# Actions that are executed on each repository
actions:
    # execute a process
    - git rev-parse
    - [git, rev-parse]
    - [[git, rev-parse], { workingDirectory: /some/other/pass, env: { ENV: VALUE }, timeout: 300, verbose: true }]
    # alternative syntax
    - { exec: [git, rev-parse], workingDirectory: /some/other/pass, env: { ENV: VALUE }, timeout: 300 }
    # forward to another command
    - { command: [ccabs:vcs:commit, --message, 'Do a new commit on %repository%'] }
    # group multiple actions
    -
        actions:
            - git rev-parse
            - [git, rev-parse]
            # overwrite the timeout setting for one action
            - [[git, rev-parse], { timeout: 900 }]
        # these settings will be inherited to the child-actions
        # the working directory, the default is the local repository path
        workingDirectory: /some/other/pass
        # environment variables
        env: { ENV: VALUE }
        # process timeout, the default is no timeout
        timeout: 300
        # ignore that the action has failed, otherwise an exception is thrown
        ignoreFailure: true
        # a condition to run the action(s) only under certain cases
        if: { not: { fileExists: '/some/path/that/does/NOT/exists' } }

# Actions that are executed after the execution is finished, for syntax see the actions section
# Warning: repository specific placeholders are not available here!
post:
    ...

# A list of custom variables that can be used as placeholders, note that you can not overwrite existing placeholders!
variables:
    my-path: /my/custom/path
```

Condition reference
-------------------

AND / OR condition
==================

```yaml
...
    if:
        # the first level is an implicit AND condition
        - { ... }
        - { ... }
        - { ... }
        # AND / OR conditions can be nested
        -
            or:
                - { ... }
                -
                    and: [{...}, {...}]
                - { ... }
```

Multiple conditions in one array will result in an AND condition.

```yaml
...
    if:
        # produce an implicit AND condition with two conditions...
        not: { ... }
        fileExists: { ... }
        # expect it is nested to an OR condition
        or:
            not: { ... }
            fileExists: { ... }
```

NOT condition
=============

```yaml
...
    # invert the result of another condition
    if: { not: { ... } }
```

File exists condition
=====================

```yaml
...
    # test if a file exist, placeholders are supported
    if: { fileExists: "/%dir%/to/test/for/existence" }
```

Placeholder reference
---------------------

<table>
<tbody>
	<tr><th>scheme</th><td>The scheme extracted from the repository URL.</td></tr>
	<tr><th>host</th><td>The host extracted from the repository URL.</td></tr>
	<tr><th>port</th><td>The port extracted from the repository URL.</td></tr>
	<tr><th>user</th><td>The user extracted from the repository URL.</td></tr>
	<tr><th>pass</th><td>The pass extracted from the repository URL.</td></tr>
	<tr><th>path</th><td>The path extracted from the repository URL.</td></tr>
	<tr><th>query</th><td>The query extracted from the repository URL.</td></tr>
	<tr><th>fragment</th><td>The fragment extracted from the repository URL.</td></tr>
</tbody>
<tbody>
	<tr><th>repository</th><td>Shortcut for `%owner%/%name%`.</td></tr>
	<tr><th>owner</th><td>The name of the repository owner.</td></tr>
	<tr><th>name</th><td>The name of the repository.</td></tr>
</tbody>
<tbody>
	<tr><th>dir</th><td>The local directory path inside the storage.</td></tr>
</tbody>
<tbody>
	<tr><th>ref</th><td>The working ref name, e.g. `master`.</td></tr>
	<tr><th>real-ref</th><td>The real ref name, e.g. `origin/master`.</td></tr>
	<tr><th>ref-type</th><td>The ref type, `branch`, `tag` or `commit`.</td></tr>
	<tr><th>tag</th><td>The most recent tag name.</td></tr>
	<tr><th>commit</th><td>The commit name / hash.</td></tr>
</tbody>
<tbody>
	<tr><th>author-name</th><td>The author name.</td></tr>
	<tr><th>author-email</th><td>The author email.</td></tr>
	<tr><th>author-date[:format]</th><td>The author date.</td></tr>
</tbody>
<tbody>
	<tr><th>committer-name</th><td>The committer name.</td></tr>
	<tr><th>committer-email</th><td>The committer email.</td></tr>
	<tr><th>committer-date[:format]</th><td>The committer date.</td></tr>
</tbody>
</table>
