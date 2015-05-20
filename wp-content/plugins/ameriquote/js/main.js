jQuery("document").ready(function($) {
	$("#ameriquote_wrapper").fadeIn();
	//$("#ameriquote_wrapper select").select2({dropdownCssClass: 'dropdown-ameriquote',formatResultCssClass:'container-ameriquote'})
	ko.applyBindings(new CompulifeViewModel());

	function quoteRecord(data) {
		try {
			self = this;

			self.company_name = (data.CompanyName?data.CompanyName.trim():'');
			self.health_category = (data.HealthCategory?data.HealthCategory:'');
			self.monthly = (data.Monthly?data.Monthly:'');
			self.product_name = (data.ProductName?data.ProductName.trim():'');
			self.product_category = (data.ProductCategory?data.ProductCategory:'');
			self.company_code = (data.CompanyCode?data.CompanyCode:'');
			self.product_code = (data.ProductCode?data.ProductCode:'');
			self.face_amount = (data.FaceAmount?data.FaceAmount:'');
			self.phone = (data.phone?data.phone:'');
			self.aff_id = (data.aff_id?data.aff_id:'');
			self.transaction_id = (data.transaction_id?data.transaction_id:'');
			self.health_code = (data.HealthCode?data.HealthCode.replace("+", "plus"):'');
			self.new_category = (data.NewCategory?data.NewCategory:'');
		}
		catch (err) {
			console.log(err.message);
		}




	}
	function featuredRecord(data) {
		try {
			self = this;

			self.company_name = (data.company_name?data.company_name.trim():'');
		
			
			self.product_name = (data.title?data.title.trim():'');
		
			self.company_code = (data.company_code?data.company_code:'');
			self.face_amount = (data.face_amount?data.face_amount:'');
			self.phone = (data.phone?data.phone:'');
			self.aff_id = (data.aff_id?data.aff_id:'');
			self.transaction_id = (data.transaction_id?data.transaction_id:'');
			self.new_category = (data.term?data.term:'');
			self.product_code = (data.product_code?data.product_code:'');
			self.featured_image = (data.featured_image?data.featured_image:'http://quotes.ameriquote.com/wp-content/uploads/sites/8/2015/04/ameriquote.png');
			self.application_url = (data.application_url?data.application_url:'#');
		}
		catch (err) {
			console.log(err.message);
		}




	}
	function companyRecord(data) {
		try {

		}
		catch (err) {
			console(err.message);
		}
		self = this;

		self.company_name = data.title;
		self.company_code = data.meta.company_code;
		self.content = data.content;
		self.featured_image = (data.featured_image != null ? data.featured_image.source : "http://quotes.ameriquote.com/wp-content/uploads/sites/8/2015/04/ameriquote.png");
	}



	function filterQuote(obj) {

		try {
			if(obj.product_name){
			if (obj.product_name.search("SI - ") > -1 || obj.product_name.search("Simplified Issue") > -1) {
				return obj;
			}
			else {
				return false;
			}}
		}
		catch (err) {
			console.log(err.message);
		}

	}

	function getAge(dateString) {
		try {

		}
		catch (err) {
			console(err.message);
		}
		var today = new Date();
		var birthDate = new Date(dateString);
		var age = today.getFullYear() - birthDate.getFullYear();
		var m = today.getMonth() - birthDate.getMonth();
		if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
			age--;
		}
		return age;
	}

	function numberWithCommas(x) {
	    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	function CompulifeViewModel() {

		format_telephone = function(phone_number) {
			try {


				var cleaned = phone_number.replace('/[^[:digit:]]/g', '');
				matches = cleaned.match('([0-9]{3})([0-9]{3})([0-9]{4})');
				return matches;
			}
			catch (err) {
				console(err.message);
			}
		}

		


		var self = this;

		$.jStorage.set("quotes", []);
		$.jStorage.set("featured_quotes",[]);
		self.isActive = false;
		self.company = ko.observable();
		self.builtin = new Array("Viva");
		self.call_agent = ko.observable();
		self.quotes = ko.observableArray();
		self.featured_quotes = ko.observableArray();
		self.quotes.subscribe(function(data) {
			//setUpIsotope();
			//console.log("isotope fired");
		});
		self.featured_quotes.subscribe(function(data){
			if(typeof data !=undefined){
				//console.log("featured quotes subscribe:");	
				//console.log(data);
			}
			
		});
		self.featured_quotes.extend({ notify: 'change' });
		self.results = ko.pureComputed({
			read: function() {
				return _.size(self.quotes()) > 0;
			},
			write: function(data) {
				if (!data) {
					self.quotes([]);
				}

			}

		});

		self.saveQuote = function(data) {
			console.log(data);
		}
		self.setUpIsotope = function() {
			try {
				
			console.log("Starting Isotope Set up. "+Date());
			if($(".results-grid .quote.standard-quote").length > 0 && $(".results-grid .quote.featured-quote").length > 0  ){
				console.log("Setting up Isotope");
				
				$(".results-grid").isotope();
				self.appearance();
				//console.log("Setting up Isotope");
				//console.log("# of quotes:" +$(".results-grid .quote").length );

				if (self.isActive) {
					$(".results-grid").isotope("destroy");
				}

				var $container = $(".results-grid").isotope({
					itemSelector: '.quote',
					transitionDuration: '.8s',
					layoutMode: 'masonry',
					getSortData: {
						company: '[data-company]',
						premium: '[data-premium]',
						health: '[data-health]'
					},
					sortBy: 'premium'
				});


				// console.log($container);
				$('#sorts').on('click', 'div.btn', function(e) {

					e.preventDefault();
					var sortByValue = $(this).attr('data-sort-by');
					//console.log($(this).attr('data-sort-by'));
					$('#sorts div').removeClass('btn-inverse');
					$(this).addClass('btn-inverse');
					$container.isotope({
						sortBy: $(this).attr('data-sort-by')
					});

				});

				if (!('ontouchstart' in window)) {
					$('[data-toggle="tooltip"]').tooltip();
				}
				self.isActive = !self.isActive;
			} else {
			console.log("both not loaded yet");
			}
			}
			catch (err) {
				console.log(err.message);
			}
			
		}

		self.callAgent = function(data) {
			try {


				//console.log(data.phone);
				self.call_agent(data.phone);
				$(".call-pop").modal("show");
			}
			catch (err) {
				console(err.message);
			}
		}

		self.appearance = function() {
			try {


				$(".results-grid .quote").each(function(i) {
					$(this).delay((i++) * 100).fadeTo(300, 1);
				})
			}
			catch (err) {
				console(err.message);
			}
		}
		self.featuredAppearance = function() {
			try {


				$(".results-grid .featured-quote").each(function(i) {
					$(this).delay((i++) * 100).fadeTo(300, 1);
				})
			}
			catch (err) {
				console(err.message);
			}
		}
		self.getQuotes = function(form) {
			try {

			self.quotes([]);
				var params = {
					State: $(form.State).val(),
					Birthday: $(form.Birthday).val(),
					BirthMonth: $(form.BirthMonth).val(),
					BirthYear: $(form.BirthYear).val(),
					Health: $(form.Health).val(),
					FaceAmount: $(form.FaceAmount).val(),
					Sex: jQuery("input[name=Sex]:checked").val(),
					Smoker: jQuery("input[name=Smoker]:checked").val(),
					NewCategory: $(form.NewCategory).val(),
					ModeUsed: "M",
					UserLocation: "AJAX",
					TEMPLATEFILE: "AJAX_TEMPLATE.HTM",
					HTEMPLATEFILE: "HTEMPLATE.HTM",
					CqsComparison: " Compare Now"
				}

				$.ajax({
					url: "http://quotes.ameriquote.com/proxy.php",
					data: params,
					dataType: "json",
					method: "POST",
					cache: false,
					contentType: "application/x-www-form-urlencoded",
					beforeSend: function(xhr) {
						xhr.setRequestHeader(
							'X-Requested-With', {
								toString: function() {
									return '';
								}
							}
						);
				
					},
					converters: {
						"text json": function(resp) {
							newStr = resp.trim().slice(0, -1) + "]}";
							//console.log(newStr);
							return newStr;

						}
					},
					headers: {
						"Accept": "text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8"
					},
					success: function(result) {
						$("#ameriquote_wrapper .lander").hide();
						result = jQuery.parseJSON(result);
						//console.log(result);
						if (result && result.success) {

							
							for (quote in result.results) {
								result.results[quote].phone = ameriquote_phone;
								result.results[quote].aff_id = ameriquote_aff_id;
								result.results[quote].transaction_id = ameriquote_transaction_id;
								result.results[quote].FaceAmount = numberWithCommas(result.results[quote].FaceAmount);
								quoteObj = new quoteRecord(result.results[quote]);

								if (filterQuote(quoteObj)) {
									self.quotes.push(quoteObj);
								}
							
							}
							
							
							$.jStorage.set("quotes", self.quotes());
							self.getFeatured(null,self.setUpIsotope);
						}
						else {
							console.log("error");
						}
						
					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("There was an error: " + errorThrown);
						console.log("textStatus: " + textStatus);
						console.log(jqXHR);
					},
					complete: function() {
						console.log("Get Quotes complete:" + Date())
						
					}
				});
			}
			catch (err) {
				console.log(err.message);
			}

		};

		self.postLead = function(data) {
			try {


				terms_obj = {
					"1": "1",
					"2": "5",
					"3": "10",
					"4": "15",
					"5": "20",
					"6": "25",
					"7": "30",
					"9": "35",
					"0": "40",
					"T": "65",
					"U": "70",
					"V": "75",
					"A": "80",
					"B": "85",
					"C": "90",
					"D": "95",
					"E": "100",
					"G": "105",
					"H": "110",
					"Z:1234567TUVABCDEGH": "20"
				};
				
				gender_obj = { "M":"Male","F":"Female"};
				ip = "";
				$.get("http://quotes.ameriquote.com/ip.php", function(result) {
					ip = result;
					birth_date = jQuery("#ameriquote_wrapper select[name=BirthMonth]").val() + "/" + jQuery("#ameriquote_wrapper select[name=Birthday]").val() + "/" + jQuery("#ameriquote_wrapper select[name=BirthYear]").val();

					var lead_params = {
						SRC: 'ameriquote',
						FirstName: $(data.firstname).val(),
						LastName: $(data.lastname).val(),
						Email: $(data.email).val(),
						Primary_Phone: $(data.phone).val(),
						ZipCode: $(data.zipcode).val(),
						TYPE: 19,
						Landing_Page: "all",
						Shared_Exclusive: "Exclusive",
						Address: "123 Main St",
						City: "Atlanta",
						State: "GA",
						IP_Address: ip,
						Coverage_Type: "Term",
						coverage_years: terms_obj[$(data.coverage_term).val()],
						insurance_type: "term",
						Face_Amount: $(data.face_amount).val(),
						Height_Feet: "06",
						Height_Inches: "0",
						Weight: "220",
						external: "yes",
						Age: getAge(birth_date),
						Gender: gender_obj[jQuery("#ameriquote_wrapper input[name=Sex]:checked").val()],
						Birth_Day: jQuery("#ameriquote_wrapper select[name=Birthday]").val(),
						Birth_Month: jQuery("#ameriquote_wrapper select[name=BirthMonth]").val(),
						Birth_Year: jQuery("#ameriquote_wrapper select[name=BirthYear]").val(),
						Birth_Date: jQuery("#ameriquote_wrapper select[name=BirthMonth]").val()+"/"+jQuery("#ameriquote_wrapper select[name=Birthday]").val()+"/"+jQuery("#ameriquote_wrapper select[name=BirthYear]").val(),
						Smoker: jQuery("#ameriquote_wrapper input[name=Smoker]:checked").val(),
						Felony_Conviction: "No",
						carrier: $(data.company_name).val(),
						product: $(data.product_name).val()

					}

					$.ajax({
						url: "http://snapgen2.metrixinteractive.com",
						data: lead_params,
						method: "POST",
						dataType: "xml",
						success: function(results) {
							$(data).parent().html("<div class='response'>Terrific! An Ameriquote representative will be in touch with you shortly to help you with this application and any other questions you may have.</div>");
							parent_id = $(data.product_code).val()+$(data.health_code).val();
							//console.log(results);
							var $container = $(".results-grid").isotope();
							$("#ameriquote_wrapper .quote").each(function(i){
								if($(this).attr("id")!=parent_id){
									$(this).fadeOut();
									$container.isotope("remove",this);
								}
							});
							
						$container.isotope('layout');	
						},
						error: function(jqXHR, textStatus, errorThrown) {
							console.log("There was an error: " + errorThrown);
							console.log("textStatus: " + textStatus);
							console.log(jqXHR);
						},
						complete: function() {
							//self.setUpIsotope();
						}
					})
					console.log(lead_params);
				});
			}
			catch (err) {
				console.log(err.message);
			}



			//snapgen2.metrixinteractive.com/?TYPE=19&SRC=1300degrees&Landing_Page=allwebpost&Shared_Exclusive=Exclusive&FirstName=Lorenzo&LastName=Miller&Primary_Phone=7706507926&Secondary_Phone=4698&Email=ellamill%40yahoo.com&
			//Address=123 Main St&City=Atlanta&State=GA&ZipCode=30076&Code=&County=&IP_Address=38.110.122.215&Coverage_Type=Term+20&coverage_years=20&insurance_type=term&Face_Amount=500000&Height_Feet=06&Height_Inches=0&Weight=220&Age=49&Birth_Date=06%2F05%2F1965&Birth_Day=05&Birth_Month=06&Birth_Year=1965&
			//Gender=Male&Smoker=No&Felony_Conviction=No&Hazardous_Activities=No&Private_Pilot=No&Major_Med_Conditions=No&Med_Conditions_Type=&API_Action=pingPostLead&Key=Ki6yKkB8F4Byd4KMuNJM-xJtu2f5-zZRd2dauNXNdtKr-j8R-4MSgHnn&Mode=ping&Generic=1&FullMode=1&%24ping&external=yes

		}

		self.showForm = function(data) {
			try {


				id = data.product_code + data.health_code;
				//console.log(data);
				var $container = $(".results-grid").isotope();

				$("#" + id + " .apply-form").toggle();
				$container.isotope("layout");
			}
			catch (err) {
				console.log(err.message);
			}
		}
		self.showFeaturedForm = function(data) {
			try {


				id = data.product_code;
				//console.log(data);
				var $container = $(".results-grid").isotope();

				$("#" + id + " .apply-form").toggle();
				$container.isotope("layout");
			}
			catch (err) {
				console.log(err.message);
			}
		}
		
		self.getCompany = function(data) {
			try {


				//console.log(data);
				params = {
					"filter[name]": data.company_code
				};
				$.ajax({
					url: "http://quotes.ameriquote.com/wp-json/posts?type=company",
					data: params,
					dataType: "json",
					method: "GET",
					cache: false,
					success: function(result) {
						//result = jQuery.parseJSON(result);

						if (result && result.length > 0) {
							companyObj = new companyRecord(result[0]);
							self.company(companyObj);
							//console.log(self.company());	
							$(".company-pop").modal("show");
						}



					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("There was an error: " + errorThrown);
						console.log("textStatus: " + textStatus);
						console.log(jqXHR);
					},
					complete: function() {
						//self.setUpIsotope();
					}
				});
			}
			catch (err) {
				console.log(err.message);
			}
		}
		self.getFeatured = function(data, fn) {
			try {

				self.featured_quotes([]);
				$.ajax({
					url: "http://quotes.ameriquote.com/wp-json/posts?type=featured-product",
					dataType: "json",
					method: "GET",
					cache: false,
					async:false,
					success: function(result) {
						//result = jQuery.parseJSON(result);

						if (result && result.length > 0) {
						
							

							$.each(result,function(i,quote) {
								//console.log(result[quote]);
									//console.log(result);
								company_id=quote.meta.company[0];
								company_code="ameriquote";
								company_name="Ameriquote";
								featured = {};
								//console.log(featured);
								$.ajax({
									url: "http://quotes.ameriquote.com/wp-json/posts?type=company&filter[ID]="+company_id,
									dataType: "json",
									method: "GET",
									async: false,
									success: function(company_result){
										if(company_result && company_result.length > 0){
											//console.log(company_result);
											company_code=company_result[0].meta.company_code;
											company_name=company_result[0].title;
										
										
										}else{
											console.log("No company found");
										}
										//console.log(quote);
										featured.phone = ameriquote_phone;
										featured.aff_id = ameriquote_aff_id;
										featured.transaction_id = ameriquote_transaction_id;
										featured.company_name=company_name;
										featured.company_code=company_code;
										featured.term = quote.meta.term;
										featured.face_amount = "$"+numberWithCommas(quote.meta.face_amount);
										featured.title =quote.title;
										featured.product_code = quote.ID;
										featured.application_url = quote.meta.application_url;
										
										if(_.contains(quote.meta.states, $("#ameriquote_wrapper select[name='State']").val())){
											quoteObj = new featuredRecord(featured);
						
										self.featured_quotes.push(quoteObj);
										}
										
									},
									complete: function(){
											//self.setUpIsotope();
									}
									
								});
								
							

							});
							console.log("Featured Results parsed.");
						
							
						}

					

					},
					error: function(jqXHR, textStatus, errorThrown) {
						console.log("There was an error: " + errorThrown);
						console.log("textStatus: " + textStatus);
						console.log(jqXHR);
					},
					complete: function() {
						console.log("Featured Quotes Complete: "+ Date());
						$.jStorage.set("featured_quotes", self.quotes());
								if(typeof fn == "function"){
								//	console.log("calling function"+this.toString);
							fn.call(this, data);
						}
					}
				});
			}
			catch (err) {
				console.log(err.message);
			}
		}
	}

});