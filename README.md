# appserver-io-cli
This is a cli program for the appserver.io environment.

Commands to be made available in the CLI.

- [x] "about" command. This is the default and explains how the CLI works.

- [ ] "new" command. creates a new project.

  - Arguments/ Options:
    - [x] **name** - _mandatory_ - The name of the project and also the name of the directory under `/webapps`.
    - [x] **[--with]** - _optional_ - the "with" parameter allows you to add different applications into the project automatically. For instance "--with routlt" will install the project with the `routlt`package. "--with example" will install the example app automatically. 
      
  - Questions/ Parameters:
    - [x] If the name is missing from command, ask for it.
    - [x] Ask for directory name.
    - [x] Ask for org name      

  - Tasks to do:
     - [x] Create project/ application directory.
     - [x] Create git repo.
     - [ ] Create composer project.
     - [ ] Create skeleton app.
     - [ ] If a package was added with ´--with´, install it.
       

- [ ] "restart" command. This is for restarting appserver. 

- [ ] "virtual-host" command. To create a new virtual host for a particular app. The app must be created first.

  - Arguments:
    **[name-of-directory]** - _mandatory_ - The name of the application directory.
   - .
    
- [ ] "remove" command. To remove an application, which is no longer needed. 

- [ ] "environment" command. Changes the environment.
       
  - Arguments:
    **[prod|dev]** - To select between production or development modes.
  - .

- [ ] "mode" command. To start appserver in runner mode. 

- [ ] "scanner" command. Creates a scanner to automatically restart appserver, when changes are made to a particular application's directory. 
  - Arguments:
    **[name-of-directory]** - _mandatory_ - The name of the directory under `/webapps`, which should be watched. 
