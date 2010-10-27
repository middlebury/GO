
$(document).ready(function(){
	
	// Apply the "add/remove behavior" to the list of Aliases
	addRemoveBehavior('#alias_list','#add_alias_button','#add_alias_text');
	// Apply the "add/remove behavior" to the list of Admins
	addRemoveBehavior('#admin_list','#add_admin_button','#add_admin_text');
	
	// A function to apply "add/remove" behavior to a list of items
	// that can have items added to it, each with a delete button
	// next to it that will delete it from the list. There is also
	// an input field and an add button.
	// Is passed the ID of the list, the ID of the input textfield,
	// and the id of the add button
	function addRemoveBehavior(ul_id, button_id, text_id) {
		
		// STUFF THAT HAPPENS TO ITEMS THAT ALREADY EXIST

		// Do the following for each list element
		// in the list we're dealing with
		$(ul_id + " li").each(function(){
			// This var is how we identify each element, by its list name with its value
			var current_value = $(this).text().replace(/(^[\s\xA0]+|[\s\xA0]+$)/g, '');
			var unique_identifier = hex_md5(current_value) + "_" + ul_id.slice(1);
			// Give each list item this identifier
  		$(this).attr("class", unique_identifier);
			// Bind a function to the Delete button that deletes
			// the list item with our unique_identifier
			$(ul_id + ' li.'+ $(this).attr('class') +'>input').bind("click", function(){
				$(ul_id + " li").remove("." + unique_identifier);
				$("<input type='hidden' class='" + unique_identifier + "' name='" + ul_id.slice(1) + "_del[]' value='" + current_value + "' />").prependTo('form');
			}) // End $(ul_id + ' li>input').bind("click", function(){
		}); // End $(ul_id + " li").each(function(){
		
		// STUFF THAT HAPPENS TO ITEMS THAT ARE CREATED ON THE FLY
		
		// Do all this stuff when the "add" button is clicked
		$(button_id).bind("click", function(){
			// The value the user wants to add (trim it to ignore trailing/leading spaces)
			var value_to_add = $(text_id).val().replace(/(^[\s\xA0]+|[\s\xA0]+$)/g, '');
			//alert(value_to_add.trim());
			// a flag to be set if the input is not valid
			var invalid_value = 0;
			
			if (value_to_add.match(/[^A-Za-z0-9-_\?\/\.~\+%]/)) {
				alert('Invalid characters.');
				return false;
			}
			
			// Check to see if it's valid
			// If the value is already in the list then it's invalid
			$(ul_id + " li").each(function(){
				var existing_value = $(this).text().replace(/(^[\s\xA0]+|[\s\xA0]+$)/g, '');
				if (value_to_add == existing_value) {
					invalid_value = 1;
				}
			})
			
			// If the value IS valid
			if (value_to_add.length > 0 & invalid_value != 1) {
				// This var is how we identify each element, by its list name with its value
				var unique_identifier = hex_md5(value_to_add) + "_" + ul_id.slice(1);
				// Create a new li with the new value and a unique identifier
				$("<li class='"+ unique_identifier +"'>" + $(text_id).val() + " <input type='button' value='Delete' /></li>").appendTo(ul_id);
				//Create a hidden input with the same
				$("<input type='hidden' class='" + unique_identifier + "' name='" + ul_id.slice(1) + "[]' value='" + $(text_id).val() + "' />").prependTo('form');
				// Add a function to the Delete button that
				// will delete this unique li and input
				$(ul_id + ' li.' + unique_identifier + '>input').bind("click", function(){
					// Remove the li
					$(ul_id + " li").remove("." + unique_identifier);
					// Remove the input
					$('input').remove("." + unique_identifier);
				}); // End $(ul_id + ' li.' + unique_identifier + '>input').bind("click", function(){
			} // Enf if (value_to_add.length > 0 & invalid_value != 1) {
		}) // End $(button_id).bind("click", function(){
	} // End function addRemoveBehavior(ul_id, button_id, text_id) {
}); // End $(document).ready(function(){