# cube

Installation:

1) copy .env.test to .env
2) change MAILER_DSN to send mails
3) change DATABASE_URL to make connection with db
4) change APP_ENV to 'dev' for further development

To set up db run migration commands:
- php bin/console make:migration
- php bin/console doctrine:migrations:migrate


 
