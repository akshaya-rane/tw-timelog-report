# Time log Report

This repository is based on [Teamwork](https://teamwork.com/) project management tool & [Float](https://www.float.com/) Resource management tool. This will help to send time log update reminder of previous week to all users. 

***

The current integration is based on Teamwork & Float API. Teamwork & Float already has integration for Project, Task sync etc. There is no user mapping & it is difficult to create this via Zapier too. In case you are using Teamwork purely for project management & Float for schedule management, this solution will be helpful for you. 

## Use case scenario

* There are several users on the Teamwork tool. 
* Project time is logged into respective projects inside Teamwork tool.
* Schedule for all the developers is maintained over Float system.
* In case any developer miss to log expected time as per his/her schedule i.e If time log percentage is lesser than 80% of scheduled time, this tool will help you to simply send reminder to your developers.

---
## Pre-requisites

### Teamwork account - API Key


Please follow below steps to create API key for any user on Teamwork.

- Go to your [Teamwork](https://teamwork.com/)  account.
- Create collaborator a user / you can use any existing standard/collaborator user type.
- Give access to existing projects.
- Provide permission to Give automatic access to [all future projects](https://support.teamwork.com/projects/project-people/giving-automatic-access-to-all-future-projects#). 
- You can locate API key for this account by following [this](https://support.teamwork.com/projects/using-teamwork/locating-your-api-key) tutorial. Please note down the API key.

### Float account - API key

Please follow below steps to create API key.

- Go to your [Float](https://www.float.com/) account.
- Just go to Team Settings --> Integrations. 
- Generate an [API key](https://support.float.com/en/articles/55483-api). 

***
## How to use this tool?

- You can make use of composer to install dependent phpmailer library to send notifications. 
- Please refer the reference link given to setup Auth details for Gmail SMTP setup.
- Create a credentials.json file as described below.
* Setup weekly CRON for
* * 'map_users.php' file -- Maps Teamwork & Float users.
* * 'get_timelog.php' file -- Calculate log difference from schedule & send reminder notification.

### Create credentials.json file

Please find credentials file content below,

```json
{
    "float": "<Float API Key>",
    "tw": "<Teamwork User API key>:a",
    "tw_base":"<Base URL of Teamwork project>",
    "float_base": "<Base URL of Float project>",
    "company_name": "<Your company name>",
    "reminder_email": "<Gmail account Email ID used for sending email>",
    "client_id": "<XOauth client ID>",
    "client_secret" : "<XOAuth Client secret>",
    "refresh_token": "<XOauth refresh token>",
    "summary_email_address": "<Email address where Summary email notification to send>",
    "summary_email_alias": "Alice name for Summary email"
}
```

Please add appropriate value as stated beside each key. Don't forget to remove brackets. 

You can refer to [Using Gmail with XOAUTH2](https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2) for creating Gmail XOAuth details.

Create **'credentials.json'** file & place it in the root folder of this tool.


***

## FAQ

1. How to find Teamwork project URL?

It's the URL by which you access your teamwork account.

2. Will this tool send reminder to individual recipients? 

This will send a single reminder email with all the recipients added in the sender list. 

3. Facing fatal error to send email.

Please make sure you have installed PHPMailer library & appropriate credentials are setup in credentials.json file. 

***

## Screenshots

Email Notification will be displayed as below,

![email-notification](https://user-images.githubusercontent.com/11537877/156890425-8319a543-5319-4e40-9ead-d7962ed49036.png)


## References

- [Float API Reference](https://developer.float.com/api_reference.html)
- [Teamwork API Reference](https://apidocs.teamwork.com/docs/teamwork/YXBpOjQyMjU4OTEw-api-reference-v3)
- [Using Gmail with XOAUTH2](https://github.com/PHPMailer/PHPMailer/wiki/Using-Gmail-with-XOAUTH2)
