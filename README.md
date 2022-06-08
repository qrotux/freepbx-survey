# freepbx-survey

### HOWTO

0. Have FreePBX v.15

#### Settings

Change settings section in the `_utils.php` file

#### Survey

1. Add custom module [qrotux/queues](https://github.com/qrotux/queues/tree/release/15.0)
2. Add `survey.php` and `_utils.php` files to `/var/lib/asterisk/agi-bin` directory
3. Execute `chmod +x survey.php && chown asterisk:asterisk survey.php`
4. Enable "Set Interface Var" and "Set Queue Var" in the queue (sets MEMBERINTERFACE, MEMBERNAME and QUEUENAME variables)
5. Add to configuration file "extensions_custom.conf" a survey "assessment" extension (example 1)
  1. !!! Change playback file path!
6. Create custom destination like "Survey assessment" with target "assessment,s,1"
7. Reload Asterisk settings
8. Have fun with "survey.php"

Test:
```bash
# change IS_CLI to true in survey.php
./survey.php 88001001001 101 "Internal" 200 5
# change IS_CLI back to false in survey.php
```

#### Voicemail Telegram notifications

1. Add `voicemail-callback.php` and `_utils.php` files to `/var/lib/asterisk/agi-bin` directory
2. Execute `chmod +x voicemail-callback.php && chown asterisk:asterisk voicemail-callback.php`
3. Add default extension like "199" with enabled voicemail
4. Add IVR "Queue Voicemail" with options:
  1. Add announcement like "Wait operator answer or press 1 to left message"
  2. Add digit "1" to destination "Voicemail: 199 (No message)"
5. Install module "Voicemail Admin"
  1. Go to "Settings -> Voicemail admin"
  2. Tab "Settings"
  3. Change "External Notify" value to "/var/lib/asterisk/agi-bin/voicemail-callback.php"

After this customer in queue would be have opportunity to enter "1" and left the message. This message goes to 199 extension voicemail and all voicemails should pass to script "voicemail-callback.php"

#### Example 1

```
[assessment]
exten => s,1,Answer()
exten => s,n(question),Read(answer,,1,,1,15)
;this saves the answers into the CDR field of "userfield"
exten => s,n,Set(CDR(userfield)=${answer})
;Send survey with arguments {inbound_number} {queue_member_number} {queue_member_name} {queue_name} {customer_answer}
exten => s,n,AGI(survey.php, ${CALLERID(number)}, ${MEMBERINTERFACE}, ${MEMBERNAME}, ${QUEUENAME}, ${answer})
;play a thankyou message. you can just remove this line for anyone who doesn't want the thankyou.

exten => s,n,playback(/var/lib/asterisk/sounds/ru/custom/survey-bye) ; !!! CHECK FILE PATH
exten => s,n,hangup()
```
