# Data directory

This directory contains application mutable state.

You are encouraged to write temporary files, database files and projection results in here.

## [`database.sqlite`](./database.sqlite)

The `database.sqlite` file in here can be opened with tools like IntelliJ Idea / PHPStorm or SQLite browser.

Be aware that since this is SQLite, some tools will lock the file while viewing it, which means that it may
become inaccessible.

Should you need a fresh DB, feel free to delete `database.sqlite` and re-run one of the example scripts that
generated it.
