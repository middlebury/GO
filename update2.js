
$(document).ready(function(){
	alert('hi');
	addRemoveBehavior('#alias_list','#add_alias_button','#add_alias_text');
	addRemoveBehavior('#admin_list','#add_admin_button','#add_admin_text');
	
	function addRemoveBehavior(ul_id, button_id, text_id) {
		// Add class to each li which is an index of the list element
		$(ul_id + " li").each(function(index){
  		$(this).attr("class", index);
  		//also make a hiden input for each
  		// IE7 does not support .trim()
  		if ($.browser.msie) {
  			$("<input type='hidden' name='" + ul_id.slice(1) + "[]' value='" + $(obj).text().replace(/(^[\s\xA0]+|[\s\xA0]+$)/g, '') + "' />").prependTo('form');
				// other browsers do
			} else {
				$("<input type='hidden' name='" + ul_id.slice(1) + "[]' value='" + $(this).text().trim() + "' />").prependTo('form');
			}
		});
	
		// Add a class to each "Delete" button which is an index of the li
		$(ul_id + " li>input").each(function(index){
  		$(this).attr("class", index)
		});
	
		// Add a function to each Delete button that removes the
		// <li> with the corresponding class/index
		$(ul_id + " li>input").each(function(index){
			$(this).bind("click", function(){
				var our_class = "." + index;
  			$(ul_id + " li").remove(our_class);
			})
		});
	
		// Add a function to the "Add" button
		$(button_id).bind("click", function(){
			var value = $(text_id).val();
			var invalid_value = 0;
		
			// If the value is already in the list then it's invalid
			$(ul_id + " li").each(function(){
				// IE 7 doesn't support .trim()
				if ($.browser.msie) {
					if (value == $(this).text().replace(/(^[\s\xA0]+|[\s\xA0]+$)/g, '')) {
						invalid_value = 1;
					}
				// other browsers do
				} else {
					if (value == $(this).text().trim()) {
						invalid_value = 1;
					}
				}
			});
		
			// If the value is valid then add the new value to the list
			if (value.length > 0 & invalid_value != 1) {
				$("<li>" + $(text_id).val() + " <input type='button' value='Delete' /></li>").appendTo(ul_id);
				//and create a hidden input
				$("<input type='hidden' name='" + ul_id.slice(1) + "[]' value='" + $(text_id).val() + "' />").prependTo('form');
			}
		
			// Give the new value (and all others) a class corresponding to the index (again)
			$(ul_id + " li").each(function(index){
    		$(this).attr("class", index);
			})
		
			// Give the new value's Delete button (and all others) a function that will delete the
			// corresponding li (again)
			$(ul_id + " li>input").each(function(index){
				$(this).bind("click", function(){
					var our_class = "." + index;
  				$(ul_id + " li").remove(our_class);
				})
			});
		});
	}
	
});
