# Deploy

When all the changes in development are complete and it's time to deploy,
it's important for us to know how the database should be updated.
Below are the steps to get your migrations ready for deployment.

> First of all, let me say that this scenario is not mandatory.
> If you have a better scenario in mind, you can share it with us :)

### 1. Merge Request & Conflicts

Laplus builds migrations based on two things:
1. Previous migrations
2. The current structure defined in the presents

So, to avoid problems prioritizing the generated migrations and conflicts,
it is better to merge the branches with a dev branch and create the main migrations there.

> Note: In general, changes to tables, no matter how many, can be managed.
> But there are some disadvantages that [you can read about in this section](#3-review--edit-migrations).


### 2. Generate Migrations

To create the final migrations for deployment, use the following command:

```shell
php artisan deploy:migration
```

### 3. Review & Edit Migrations

A senior can review the migrations at this stage and see that they
are not causing any problems.

A powerful feature of Laplus is that it is insensitive to the migrations being edited by
developers! So you can change them if you see a problem in any part of the migrations.

### 4. Test

To test the correctness, you can take two approaches.

The first approach is to take an output from the actual database of the deployed project
and run the migrate command on your local machine to see if you encounter any problems.
(This is a more reliable solution)

The second approach is to migrate once before creating new migrations, and then run the
migrate command again after creating new migrations.

### 5. Deploy

Run the following command to execute migrations:

```shell
php artisan migrate
```
