# LFS Development data

With the (re-)installation of this degov project comes read-only lfs_data (`docroot/profiles/contrib/degov/testing/lfs_data`), 
which holds by default (master branch) the latest db-dump and the backstopjs references.

## Requirements
### Git LFS
* Install https://git-lfs.github.com/
### GIT access
* You need write access to the repository of [`git@bitbucket.org:publicplan/degov_devel_git_lfs.git`](https://bitbucket.org/publicplan/degov_devel_git_lfs/)
* Change the remote `git remote set-url origin git@bitbucket.org:publicplan/degov_lfs_test.git`

## Run backstopjs locally via docker
``docker run -it -v $(pwd)/backstopjs:/src -v $(pwd)/lfs_data:/lfs_data backstopjs/backstopjs test``


## Failed backstopjs tests
The pipelines creates automatically new bitmaps_references after a failed test which are then provided via artifacts.
Be sure that you have reviewed the new references and that you have LFS installed before you commit the new references.

## Test new lfs_data

* Create a new feature branch for lfs_data repository (`git@bitbucket.org:publicplan/degov_devel_git_lfs.git`)
* Add and push the new data
* Create a new feature branch of the project (`git@bitbucket.org:publicplan/degov_project.git`)
* Change the branch of `degov/degov_devel_git_lfs` in the composer.json
* Add and push
* Create a new feature branch of the installation profile (`git@bitbucket.org:publicplan/degov.git`)
* Add the branch in acceptance_tests.sh so that composer use the feature branch of the project

From:

``
_composer create-project --no-progress degov/degov-project --no-install
``

To:

``
_composer create-project --no-progress degov/degov-project:dev-feature/DEGOV-123-description --no-install
``
