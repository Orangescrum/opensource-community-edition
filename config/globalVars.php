<?php

define('RELEASE', 4);
define('ASSET_RELEASE', '80'); // Change asset release on every css/js change
define('RELEASE_VESION', 'v0.1.0');
define('RELEASE_VERSION', 'v0.1.0');
define('SHOW_ARABIC', 0);

// Orangescrum Marketing Website URL
define('ORANGESCRUM_WEBSITE', 'https://www.orangescrum.com/');

// Edition / licensing identity, surfaced on the About page.
// EDITION_SOURCE_URL satisfies AGPL-3.0 section 13: network users must be able
// to obtain the corresponding source. Keep it pointed at the public repository
// that carries this build.
define('EDITION_NAME', 'Open Source Community Edition');
// Displayed version. Kept here (bind-mounted, tracked) rather than only in
// VERSION.txt, which is baked into the Docker image and can go stale until a
// rebuild — the About/What's New pages prefer this constant.
define('EDITION_VERSION', '0.1.0');
define('EDITION_VENDOR', 'Andolasoft Inc.');
define('EDITION_VENDOR_URL', 'https://www.andolasoft.com/');
define('EDITION_LICENSE_SPDX', 'AGPL-3.0-or-later');
define('EDITION_LICENSE_NAME', 'GNU Affero General Public License v3.0 or later');
define('EDITION_LICENSE_URL', 'https://www.gnu.org/licenses/agpl-3.0.html');
define('EDITION_SOURCE_URL', 'https://github.com/Orangescrum/opensource-community-edition');
// Where users go to license Orangescrum without the AGPL's obligations,
// or to buy the Enterprise edition.
define('EDITION_COMMERCIAL_URL', 'https://www.orangescrum.com/contact-us');

define('TIMELIMIT', 3000);
define('GMT_DATETIME', gmdate('Y-m-d H:i:s'));
define('GMT_DATE', gmdate('Y-m-d'));
define('GMT_TIME', gmdate('H:i:s'));
define('MAX_FILE_SIZE', 200); //In Mb
define('CASE_PAGE_LIMIT', 30); // Task Listing Page
define('MILESTONE_PAGE_LIMIT', 5);
define('PROJECT_PAGE_LIMIT', 30);
define('MILE_PAGE_LIMIT', 30);
define('USER_PAGE_LIMIT', 30);
define('ARC_PAGE_LIMIT', 30);
define('MAX_SPACE_USAGE', 1024);
define('MILESTONE_PER_PAGE', 3);
define('ARC_CASE_PAGE_LIMIT', 10);
define('ARC_FILE_PAGE_LIMIT', 10);
define('TEMP_PROJECT_PAGE_LIMIT', 10);
define('TEMP_TASK_PAGE_LIMIT', 10);
define('TASK_SEARCH_LIMIT', 30);

define('INACT_CASE_PAGE_LIMIT', 10);
define('INACT_TASK_GROUP_CASE_PAGE_LIMIT', 5);
define('FILTER_PAGE_LIMIT', 30);

define('ONBORDING', 1);
define('ONBORDING_DAILY_UPDATE', 1);
define('ONBORDING_DATE', '2013-10-10');

define('COOKIE_TIME', time() + 3600 * 24 * 180);
define('COOKIE_REM', time() + 3600 * 24 * 180);

define('CSS_PATH', HTTP_ROOT . 'css/');
define('JS_PATH', HTTP_ROOT . 'js/');
define('CSV_PATH', WWW_ROOT . 'csv' . DS);
define('DOWNLOAD_TASK_PATH', WWW_ROOT . 'DownloadTask' . DIRECTORY_SEPARATOR);
define('DOWNLOAD_S3_TASK_PATH', 'DownloadTask/zipTask/');
define('LOGTIME_CSV_PATH', CSV_PATH . 'logtime_csv' . DIRECTORY_SEPARATOR);
define('TASKLIST_CSV_PATH', CSV_PATH . 'tasks_csv' . DIRECTORY_SEPARATOR);

//Image Display path
define('HTTP_IMAGES', HTTP_ROOT . 'img/');
define('HTTP_CASE_FILES', HTTP_FILES . 'case_files/');
define('HTTP_USER_PHOTOS', HTTP_FILES . 'photos/');
define('HTTP_PROJECT_LOGO', HTTP_FILES . 'project_logo/');

define('EPIC_DATE', '2022-10-17');
define('STATIC_CONTENT_BUCKET_NAME', '');
define('STATIC_CONTENT_S3_PATH', '');
define('CUSTOMER_SUPPORT_URL', 'http://helpdesk.orangescrum.com/contact-support/');
define('KNOWLEDGEBASE_URL', 'http://helpdesk.orangescrum.com/knowledge-base/');
define('KNOWLEDGEBASE_CATEGORY_URL', 'http://helpdesk.orangescrum.com/knowledge-base-category/');

$emailHeader = '';
define('EMAIL_HEADER', $emailHeader);
$emailFooter = "<tr>
                    <td align='left' style='font:11px Verdana;color:#848484;padding-top:20px;'>
                        <div style='line-height:20px;padding-top:10px;padding-bottom:5px;font-size:14px;'>
                            Regards,<br/>
                            The Orangescrum Team
                       </div>
                        <br/>
                        If you have any questions, please write to us at <a href='mailto:" . SUPPORT_EMAIL . "'>" . SUPPORT_EMAIL . "</a>, we will be happy to help.
                        <br/>
                        (This message comes from an unmonitored alias. Please do not reply directly.)
                        <br/>

                        <div style='font-size:11px;color:#5A5A5A;font-family:verdana;padding-top:12px;'>
                            This message is distributed by Andolasoft, San Jose, CA, 95124.
                        </div>
                    </td>
		</tr>
		<tr>
                    <td align='left' style='padding:5px 0px'>
                        <hr style='border: none; height: 0.1em; color:#DBDBDB;background:#DBDBDB;'/>
                    </td>
		</tr>
		<tr>
                    <td align='left' style='font:9px Verdana;padding-top:2px;color:#737373'>
                        You are receiving this email notification because you have subscribed to Orangescrum, to unsubscribe, please email with subject 'Unsubscribe' to <a href='mailto:" . SUPPORT_EMAIL . "'>" . SUPPORT_EMAIL . '</a>
                    </td>
		</tr>
		';
define('EMAIL_FOOTER', $emailFooter);


$newEmailHeader = '<table bgcolor="#F0F0F0" border="0" cellspacing="0" cellpadding="0" width="100%">
                        <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:center;padding-top:10px;"><a href=""><img src="' . HTTP_ROOT . 'img/logo/logo-sm.svg" alt="Orangescrum" title="Orangescrum" style="display:inline-block; width:50px; height:50px;border-bottom-right-radius:8px;border-bottom-left-radius:8px;" border="0"></a>
                            </td>
                        </tr>
                    </table>';
define('NEW_EMAIL_HEADER', $newEmailHeader);


$year = gmdate('Y');
$newEmailFooter = 'Copyright ' . $year . ' Orangescrum. All Rights Reserved.<br>
                    2059 Camden Ave. #118, San Jose, CA 95124, USA
                    <br><br>
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="http://blog.orangescrum.com/">Blog</a>
                    .
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="http://www.orangescrum.com/how-it-works">How it Works</a>
                    .
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="http://www.orangescrum.com/help">Help</a>
                    .
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="http://www.orangescrum.com/aboutus">About Us</a>

                    <br><br>
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="https://twitter.com/theorangescrum"><img src="' . HTTP_ROOT . 'img/tw.png" alt="Twitter" style="width:32px;height:32px"></a>
                    <a style="color:#2489B3; font-weight:normal; text-decoration:underline;" href="https://www.facebook.com/pages/Orangescrum/170831796411793"><img src="' . HTTP_ROOT . 'img/fb.png" alt="Facebook" style="width:32px;height:32px"></a>

                    <br><br>';
define('NEW_EMAIL_FOOTER', $newEmailFooter);
