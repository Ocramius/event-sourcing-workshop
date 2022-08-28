### Send an alert when the temperature is below freezing point

Target: understanding policies and the process manager.

Edit and run [alert-if-temperature-below-zero.php](alert-if-temperature-below-zero.php).

1. create a new `WhenTemperatureBelowZeroSendAlert` [`Policy`](../../src/EventSourcing/Domain/Policy.php)
2. create a new `SendTemperateBelowZeroAlert` [`Command`](../../src/Commanding/Domain/Command.php)
3. create a new `HandleSendTemperateBelowZeroAlert` [`CommandHandler`](../../src/Commanding/Infrastructure/CommandHandler.php)
   It should only print some alert message to `STDERR` via `error_log()`, for now.
4. wire it together with [`ProcessPolicies`](../../src/EventSourcing/Infrastructure/ProcessManager/ProcessPolicies.php)
5. run it, see if you can get the alerts fired

Question: what happens when you run the script multiple times?
Question: what happens when new events appear, and you run the script again?
Question: how should we deal with failures/crashes here?