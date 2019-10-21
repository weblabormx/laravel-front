# Front

_This package was inspired on Laravel Nova._

Front is a administration panel for Laravel. It allows you to create CRUD easily in minutes. It allows to fully customize any part of the code.

*Differences with Laravel Nova:* 

- Front is created just with PHP, so there isn't any dependency with any javascript framework.
- It tries to be simpler to modify and adapt to any use.
- Menu is not generated automatically

## Resources

A resource is a class that allows you to configure all the information about the front end of a Laravel Model. Things as fields, url, buttons and more are configured on this file.

### Registering resources

The front resources are saved by default on `App\Front` folder. You can generate a new front using the artisan command:

```
php artisan front:resource Page
```

And then you will need to add the route. This will generate a access on `/models` (It uses the plural name)

```
Route::front('Model');
```

### Working with resources

There are some basic variables that can be added on the resource

```
public $title;		// Field name of the title (Name is the default value)
public $label;		// Name of the resource (Generated automatically if empty)
public $base_url;	// Url created on routes (Required)
```

### Modifying query of results

If you want to modify the results of CRUD you are able to modify it with `indexQuery` function

```
public function indexQuery($query)
{
    return $query->where('team_id', 1)->with(['user'])->latest();
}
```

### Pagination

By default a pagination is created with 50 elements, if you want to modify the quantity you can add a new attribute called `pagination` on the resource

```
public $pagination = 50;
```

## Fields

Each resource contains a `fields` method, where it returns an array with the list of all the fields that the resource should have.

```
use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Inputs\HasMany;
use WeblaborMx\Front\Inputs\Date;
use WeblaborMx\Front\Inputs\Boolean;

public function fields()
{
    return [
        ID::make(),
        Text::make('Name')->rules('required'),
        Text::make('Email')->rules(['email', 'required']),
        Text::make('Telephone')->rules('required'),
        Boolean::make('One Off Is Active')->default(true),
        Boolean::make('Recovery Is Active')->default(true),
        Date::make('Updated At')->exceptOnForms(),
        HasMany::make('Reservation'),
        HasMany::make('ClientSearch'),
    ];
}
```

### Field column convention

As noted above, Front will "snake case" the displayable name of the field to determine the underlying database column. However, if necessary, you may pass the column name as the second argument to the field's make method:

```
Text::make('Name', 'name_column')
```

### Showing / Hidding Fields

- hideFromIndex
- hideFromDetail
- hideWhenCreating
- hideWhenUpdating
- onlyOnIndex
- onlyOnDetail
- onlyOnForms
- onlyOnEdit
- onlyOnCreate
- exceptOnForms

You may chain any of these methods onto your field's definition in order to instruct Frpmt where the field should be displayed:

```
Text::make('Name')->hideFromIndex()
```

### Field Panels

If your resource contains many fields, your resource "detail" screen can become crowded. For that reason, you may choose to break up groups of fields into their own "panels":

You may do this by creating a new Panel instance within the fields method of a resource. Each panel requires a name and an array of fields that belong to that panel:

```
use WeblaborMx\Front\Inputs\ID;
use WeblaborMx\Front\Inputs\Text;
use WeblaborMx\Front\Components\Panel;

public function fields()
{
    return [
        Panel::make('General Information', $this->generalInformationFields()),
    ];
}

public function generalInformationFields()
{
    return [
        ID::make('ID'),
        Text::make('Code')->exceptOnForms(),
        Text::make('Name')->rules('required'),
    ];
}
```

### Field types

All the fields available on front:

- Autocomplete
- BelongsTo
- Boolean
- Check
- Checkboxes
- Code
- Date
- DateTime
- Disabled
- HasMany
- Hidden
- ID
- Input
- Money
- MorphMany
- MorphTo
- MorphToMany
- Number
- Password
- Select
- Text
- Textarea
- Trix

