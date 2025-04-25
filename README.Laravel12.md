# Laravel Front - Laravel 12 Compatibility

## Changes Made

This branch contains the following changes to make the package compatible with Laravel 12:

1. **Removed LaravelCollective/Html dependency**
   - The package `laravelcollective/html` is not compatible with Laravel 12
   - Created a custom form helper implementation to replace the functionality

2. **Added custom Form implementation**
   - Created `WeblaborMx\Front\Support\FormHelper` class that provides the same functionality as LaravelCollective/Html
   - Created `WeblaborMx\Front\Facades\Form` facade to maintain the same API
   - Updated all references to `Form::` in the codebase to use our custom implementation

3. **Updated Laravel version constraint**
   - Changed Laravel framework requirement from `>5.5` to `>5.5 <13.0` to explicitly support Laravel 12

## Implementation Details

### Custom Form Helper

The custom form helper class (`FormHelper.php`) provides all the form generation functionality that was previously provided by LaravelCollective/Html:

- Form opening and closing
- Text, password, hidden, number, date, file inputs
- Textarea fields
- Select dropdowns
- Checkboxes
- Submit buttons

### Migration Path

To migrate an existing project:

1. Update to this version of the package
2. No other changes should be required as the API remains the same

## Notes

- The custom form implementation maintains the same API as LaravelCollective/Html
- All blade templates and PHP code using `Form::` should continue to work without changes
- This implementation is more lightweight than the original LaravelCollective/Html package

## Testing

This implementation has been tested with Laravel 12 and works correctly with all form generation functionality.
