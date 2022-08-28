# Tracking the last recorded temperature at each location

Target: understanding projections basics.

Edit and run [project-last-temperature.php](project-last-temperature.php).

1. create an iterator over the event stream (tip: use the given `$kernel`)
2. generate an `array<string, float>` containing the last known temperature at each location
3. save the generated map

Question: what happens when you run the script multiple times?
Question: can you record new temperatures and make them affect your state?