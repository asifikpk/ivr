Þ    L      |  e   Ü      p  	   q     {       Z     Z   ì  )   G     q     ~       Q  ¨  &   ú     !	     ;	  &   P	     w	  *   	     ¼	  Û   Ð	     ¬
     ´
     »
  ?   Ê
  $   
     /     H  B   T          £     ¬  &   ¹     à  
   ä  *   ï               .     :     N     W     j     r  f        î               $     <     N  K   S  U     È   õ  l   ¾  P   +     |            Q  ¨     ú               (     8     P     V     u               ³     ¿     Ú  	   î     ø  
          -     «  M     ù            «   2  i   Þ  H   H       0   ¡     Ò  ®  ë  +        Æ     å  +   ÿ     +  5   J       E       Ö     æ     í  E   þ  E   D             K   §     ó            0   '     X     _  *   f               ¢     µ     Æ     Ó     ì     ô  ¨        »     Ë     á     ÷          &  a   -  i     Ú   ù  r   Ô  j   G     ²     ¹     Æ    Þ     ç!     ú!     "     2"     Q"     m"  *   "     «"     ¸"  /   ×"     #     #      4#  	   U#  3   _#     #      #  9   £#         1       D                      +   6   !      )   H   C                               5   &                                   ,   /   4   =   I      $   2   <      #       ;   (   9         L          G   B   "       :      *      K   .                             %   F   	   @          8          J   '   ?   
   3                 7   0   E       >   -       A       Add Entry Add IVR Add a new IVR After playing the Invalid Retry Recording the system will replay the main IVR Announcement After playing the Timeout Retry Recording the system will replay the main IVR Announcement Amount of time to be considered a timeout Announcement Append Original Announcement Applications Check this box to have this option return to a parent IVR if it was called from a parent IVR. If not, it will go to the chosen destination.<br><br>The return path will be to any IVR that was in the call path prior to this IVR which could lead to strange results if there was an IVR called in the call path but not immediately before this Checking for invalid_append_announce.. Checking for invalid_id.. Checking for retvm.. Checking for timeout_append_announce.. Checking for timeout_id.. Checking if announcements need migration.. Completely disabled Creates Digital Receptionist (aka Auto-Attendant, aka Interactive Voice Response) menus. These can be used to send callers to different locations (eg, Press 1 for sales) and/or allow direct-dialing of extension numbers. Default Delete Delete IVR: %s Delete this entry. Dont forget to click Submit to save changes! Deprecated Directory used by %s IVRs Description of this ivr. Destination Destination to send the call to after Invalid Recording is played. Direct Dial Disabled Edit IVR: %s Enabled for all extensions on a system Ext Extensions Greeting to be played on entry to the Ivr. IVR IVR Description IVR Entries IVR General Options IVR Name IVR Options (DTMF) IVR: %s IVR: %s / Option: %s If checked, upon exiting voicemail a caller will be returned to this IVR if they got a users voicemail Invalid Destination Invalid Recording Invalid Retries Invalid Retry Recording Name of this IVR. None Number of times to retry when no DTMF is heard and the IVR choice timesout. Number of times to retry when receiving an invalid/unmatched response from the caller Prompt to be played before sending the caller to an alternate destination due to the caller pressing 0 or receiving the maximum amount of invalid/unmatched responses (as determined by Invalid Retries) Prompt to be played when an invalid/unmatched response is received, before prompting the caller to try again Provides options for callers to direct dial an extension. Direct dialing can be: Return Return to IVR Return to IVR after VM There are %s IVRs that have the legacy Directory dialing enabled. This has been deprecated and will be removed from future releases. You should convert your IVRs to use the Directory module for this functionality and assign an IVR destination to a desired Directory. You can install the Directory module from the Online Module Repository Timeout Timeout Destination Timeout Recording Timeout Retries Timeout Retry Recording added adding announcement_id field.. already migrated digits pressed dropping announcement field.. fatal error migrate to recording ids.. migrated %s entries migrating no announcement field??? not needed ok posting notice about deprecated functionality Project-Id-Version: FreePBX 2.10.0.9
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2013-11-05 19:32+0900
PO-Revision-Date: 2014-04-17 10:11+0200
Last-Translator: Kevin <kevin@qloog.com>
Language-Team: Japanese <http://git.freepbx.org/projects/freepbx/ivr/ja/>
Language: ja_JP
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=1; plural=0;
X-Generator: Weblate 1.9-dev
 ã¨ã³ããªã¼ãè¿½å  IVRãè¿½å  æ°è¦IVRãè¿½å  çºä¿¡èããç¡å¹ãªå¿ç­ãåä¿¡ããå ´åã¯ãã¨ã©ã¼ãªãã©ã¤ã®é³å£°ãåçãã¦ããã¡ã¤ã³IVRã®ã¢ãã¦ã³ã¹ãããä¸åº¦åçããã¾ã åã¿ã¤ã ã¢ã¦ãé³å£°ãåçããå¾ãã¡ã¤ã³IVRã®ã¢ãã¦ã³ã¹ãååº¦åçããã¾ã ãã®æéãçµéããå¾ã«ã¿ã¤ã ã¢ã¦ãã¨ã¿ãªãã¾ãã ã¢ãã¦ã³ã¹ ãªãªã¸ãã«ã¢ãã¦ã³ã¹ãä»ãå ãã ã¢ããªã±ã¼ã·ã§ã³ è¦ªIVRããå¼ãè»¢éãããå ´åãè¦ªIVRã¸æ»ãã«ã¯ãã®ããã¯ã¹ã«ãã§ãã¯ãå¥ãã¦ãã ããããã§ãã¯ãå¥ããªãå ´åãå¼ãæ±ºããããå®åã«è»¢éããã¾ãã<br><br> ãã£ã¦ãè¦ªIVRããã®IVRã®ç´åã§ãªãå ´åã¯ãäºæããªãçµæãå¼ãèµ·ããã¦ãã¾ãå¯è½æ§ãããã¾ããæ³¨æ:è¦ªIVRã¨ã¯ããã®IVRã«è»¢éãããåã«ãã£ãIVRã®äº invalid_append_announceããã§ãã¯ä¸­.. invalid_idããã§ãã¯ä¸­.. retvmããã§ãã¯ä¸­.. timeout_append_announceããã§ãã¯ä¸­.. timeout_idããã§ãã¯ä¸­.. ã¢ãã¦ã³ã¹ã«ç§»è¡ãå¿è¦ããã§ãã¯ä¸­.. å®å¨ã«ç¡å¹ ãã¸ã¿ã«ã¬ã»ãã·ã§ãã¹ãã¡ãã¥ã¼ (èªåå¿ç­ãIVRã¨ãã¦ç¥ããã¦ãã) ãä½æãã¾ãããããã¯çºä¿¡èãç°ãªãå ´æã«ç§»åãããã®ã«ä½¿ç¨ãã¾ããä¾:(1çªãæ¼ããå ´åã¯å¶æ¥­) ããã«/ãããã¯ãåç·çªå·ãç´æ¥ãã¤ã¤ã«ã§ããããã«ãã¾ãã ããã©ã«ã åé¤ IVRãåé¤: %s ãã®ã¨ã³ããªã¼ãåé¤ãããå¤æ´é©ç¨ãå¿ããã«ï¼ %s ã®IVRããä½¿ç¨ããã¦ããå»æ­¢ããããã£ã¬ã¯ããª ãã®IVRã®èª¬æã å®å ä¸æ­£æä½ã®é²é³ãåçããå¾ã«çºä¿¡èãè»¢éããå®åã ãã¤ã¬ã¯ããã¤ã¤ã« ç¡å¹ IVRãç·¨é: %s ã·ã¹ãã ã®å¨ã¦ã®åç·ã«å¯¾ãã¦æå¹ çªå· åç· IVRã«å¥ã£ãæã«åçããé³å£°ã IVR IVRã®èª¬æ IVRã¨ã³ããªã¼ IVR ä¸è¬è¨­å® IVRã®åå IVRãªãã·ã§ã³(DTMF) IVR: %s IVR: %s / ãªãã·ã§ã³: %s ãã§ãã¯ãå¥ããå ´åããããã¤ã¹ã¡ã¼ã«ãåä¿¡ããæã«ã¯ãæ¢å­ã®ãã¤ã¹ã¡ã¼ã«ã«ããã¦ãçºä¿¡èã¯ãã®IVRã«æ»ã£ã¦ãã¾ã ä¸æ­£è»¢éå ä¸æ­£æä½ã®é³å£° ä¸æ­£ãªãã©ã¤æ° ä¸æ­£ãªãã©ã¤é³å£° ãã®IVRã®ååã ãªã DTMFãèãããã«ãIVRé¸æãã¿ã¤ã ã¢ã¦ãããå ´åã«ãªãã©ã¤ããåæ°ã çºä¿¡èããä¸æ­£ã»ä¸è´ããªãã¬ã¹ãã³ã¹ãåä¿¡ããã¨ãã«ãªãã©ã¤ããåæ°ã çºä¿¡èãå¥ã®è»¢éåã«è»¢éããåã«åçããé³å£°ãçºä¿¡èã 0 ãæ¼ããå ´åãæå¤§ä¸æ­£ã»ä¸è´ããªãå¿ç­(ä¸æ­£ãªãã©ã¤è¨­å®ã®å¤)æ°ãåä¿¡ããã¨ãã«åçãã¾ãã ä¸æ­£ã»ä¸è´ããªãå¿ç­ãåä¿¡ããå ´åã«ãã¦ã¼ã¶ã¼ã«åè©¦è¡ãä¿ãåã«åçããé³å£° ç´æ¥åç·ã«æãããã¤ã¬ã¯ããã¤ã¤ã«ãªãã·ã§ã³ãæä¾ãã¾ããå¯è½ãªè¨­å®ã¯: æ»ã IVRã«æ»ã VMã®å¾ã«IVRã«æ»ã ã¬ã¬ã·ã¼ãã£ã¬ã¯ããªãã¤ã¤ãªã³ã°ãæå¹ã«ãªã£ã¦ããIVRã %s ããã¾ãããã®æ©è½ã¯å»æ­¢ããã¦ãã¦ãå°æ¥ã®ãªãªã¼ã¹ã§ã¯åé¤ãããã§ãããããã®æ©è½ãä½¿ç¨ãã¦IVRã®å®åãå¸æãã£ã¬ã¯ããªã¸å²ãå½ã¦ãããã«ãã£ã¬ã¯ããªã¢ã¸ã¥ã¼ã«ãä½¿ç¨ããã«ã¯ãããªãã®IVRãã³ã³ãã¼ãããã¹ãã§ãããªã³ã©ã¤ã³ã¢ã¸ã¥ã¼ã«ã¬ãã¸ããªãããã£ã¬ã¯ããªã¢ã¸ã¥ã¼ã«ã®ã¤ã³ã¹ãã¼ã«ãå¯è½ã§ã ã¿ã¤ã ã¢ã¦ã ã¿ã¤ã ã¢ã¦ãæã®å®å ã¿ã¤ã ã¢ã¦ãé³å£° ã¿ã¤ã ã¢ã¦ããªãã©ã¤ åã¿ã¤ã ã¢ã¦ãé³å£° è¿½å ãã¾ãã announcement_id ãã£ã¼ã«ããè¿½å .. ç§»è¡æ¸ã¿ çºä¿¡èãå¥åããæ°å­ ã¢ãã¦ã³ã¹ãã£ã¼ã«ãããã­ãã.. è´å½çãªã¨ã©ã¼ é²é³IDã«ç§»è¡ä¸­.. %sã¨ã³ããªã¼ãç§»è¡ãã ç§»è¡ä¸­ ã¢ãã¦ã³ã¹ãã£ã¼ã«ããããã¾ãã??? å¿è¦ãªã ok å»æ­¢ãããæ©è½ã«ã¤ãã¦ã®éç¥ãæç¨¿ãã 