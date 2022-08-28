# Tracking the average recorded temperature at each location, but in the database

Target: understanding DB tables as projections, and how to generate them. 

Edit and run [project-last-temperature.php](project-average-temperature.php).

1. write a DB migration with your own table (see [existing migrations](../../src/EventSourcing/Infrastructure/Migration))
2. write a [`DbTableProjectionDefinition`](../../src/EventSourcing/Infrastructure/Projection/DbTableProjectionDefinition.php)
   that defines what we should do for each event.
   See [an example projection definition](../../test/EventSourcing/Example/Infrastructure/Projection/PendingGoodbyes.php)
   for inspiration.
3. create a [`ProcessProjectionOnTable`](../../src/EventSourcing/Infrastructure/Projection/ProcessProjectionOnTable.php)
   and run it
4. run your script, observe the database state