
$(document).ready(function(){
	
	// Aliases
	
	// Add class to each li which is an index of the list element
	$("#alias_list li").each(function(index){
  	$(this).attr("class", index)
	});
	
	// Add a class to each "Delete" button which is an index of the li
	$("#alias_list li>input").each(function(index){
  	$(this).attr("class", index)
	});
	
	// Add a function to each Delete button that removes the
	// <li> with the corresponding class/index
	$("#alias_list li>input").each(function(index){
		$(this).bind("click", function(){
			//alert('clicked');
			var our_class = "." + index;
			//alert(index);
  		$("#alias_list li").remove(our_class);
		})
	});
	
	// Add a function to the "Add Alias" button
	$("#add_alias_button").bind("click", function(){
		//alert('clicked');
		var alias = $("#add_alias_text").val();
		var invalid_alias = 0;
		
		// If the alias is already in the list then it's invalid
		$("#alias_list li").each(function(){
			//for(var i = 0; i > $(this).text().length; i++) {
				if (alias == $(this).text().trim()) {
					invalid_alias = 1;
				}
			//}
		});
		
		// If the alias is valid then add the new alias to the list
		if (alias.length > 0 & invalid_alias != 1) {
			//alert($("#add_alias_text").val());
			$("<li>"+$("#add_alias_text").val()+" <input type='button' value='Delete' /></li>").appendTo("#alias_list");
		}
		
		// Give the new alias (and all others) a class corresponding to the index (again)
		$("#alias_list li").each(function(index){
    	$(this).attr("class", index)
		})
		
		// Give the new alias' Delete button (and all others) a function that will delete the
		// corresponding li (again)
		$("#alias_list li>input").each(function(index){
			$(this).bind("click", function(){
				//alert('clicked');
				var our_class = "." + index;
				//alert(index);
  			$("#alias_list li").remove(our_class);
			})
		});
	});
	
	// Admins
	
	// Add class to each li which is an index of the list element
	$("#admin_list li").each(function(index){
  	$(this).attr("class", index)
	});
	
	// Add a class to each "Delete" button which is an index of the li
	$("#aadmin_list li>input").each(function(index){
  	$(this).attr("class", index)
	});
	
	// Add a function to each Delete button that removes the
	// <li> with the corresponding class/index
	$("#admin_list li>input").each(function(index){
		$(this).bind("click", function(){
			//alert('clicked');
			var our_class = "." + index;
			//alert(index);
  		$("#admin_list li").remove(our_class);
		})
	});
	
	// Add a function to the "Add Admin" button
	
	$("#add_admin_button").bind("click", function(){
		//alert('clicked');
		var admin = $("#add_admin_text").val();
		var invalid_admin = 0;
		
		// If the alias is already in the list then it's invalid
		$("#admin_list li").each(function(){
			//for(var i = 0; i > $(this).text().length; i++) {
				if (admin == $(this).text().trim()) {
					invalid_admin = 1;
				}
			//}
		});
		
		// If the admin is valid then add the new alias to the list
		if (admin.length > 0 & invalid_admin != 1) {
			//alert($("#add_alias_text").val());
			$("<li>"+$("#add_admin_text").val()+" <input type='button' value='Delete' /></li>").appendTo("#admin_list");
		}
		
		// Give the new admin (and all others) a class corresponding to the index (again)
		$("#admin_list li").each(function(index){
    	$(this).attr("class", index)
		})
		
		// Give the new admin's Delete button (and all others) a function that will delete the
		// corresponding li (again)
		$("#admin_list li>input").each(function(index){
			$(this).bind("click", function(){
				//alert('clicked');
				var our_class = "." + index;
				//alert(index);
  			$("#admin_list li").remove(our_class);
			})
		});
	});
	
	
});
