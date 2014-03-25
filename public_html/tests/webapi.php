<!DOCTYPE html>
<html>
    <head>
        <title>WebApi tests</title>        
        <script src="../lib/jquery/jquery-1.9.1.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="../lib/jquery-ui/js/jquery-ui-1.10.4.custom.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="../lib/knockout/knockout-2.3.0.js" type="text/javascript" charset="utf-8"></script>
        
        <script src="../lib/jquery-plugins/jstree/dist/jstree.min.js"></script>        
        <link rel="stylesheet" href="../lib/jquery-plugins/jstree/dist/themes/default/style.min.css" />
        
		<link rel="stylesheet" href="../lib/jquery-ui/css/ui-lightness/jquery-ui-1.10.4.custom.min.css">

		<style type="text/css">
			.ui-accordion {
				position: relative;
			}
			.ui-accordion .accordion-link {
				position: absolute;
				margin-top: 8px;
				z-index: 1;
				width: 16px;
				height: 16px;
				background-image: url(../lib/jquery-ui/css/ui-lightness/images/ui-icons_ef8c08_256x240.png);
			}

			.success {
				right: 2%;
				background-position: -208px -192px;
			}

			.error {
				right: 2%;
				background-position: -32px -192px;
			}

			.parseerror {
				right: 2%;
				background-position: 0 -144px;	
			}
			
			.refresh {
				right: 4%;
				background-position: -64px -80px;
			}

			
		</style>

        <script src="./webapi.test.js"></script>

        <script type="text/javascript" defer="defer">
		$(function() {

			var testModel = function(tests) {
				var self = this;
				self.tests = ko.observableArray(tests);
			 
				self.addTest = function(test) {
					self.tests.push({
						id: test.id,
						service: test.service,
						resource: test.resource,
						query: test.query,
						result: test.result,
						resultCode: test.resultCode,
						responseText: test.responseText
					});
				};
			 
				self.removeTest = function(tests) {
					self.tests.remove(tests);
				};

				self.removeTests = function() {
					self.tests.removeAll();
				};
			 
			};
			 
			var viewModel = new testModel();
			
			ko.applyBindings(viewModel);

			var baseUrl = "/webapi/";
			//var counter = 0;
			
			function buildUrl(testObject) {
				var url = baseUrl + testObject.service + "/" + testObject.resource + "?";
				if("filter" in testObject) url = url + "$filter=" + testObject.filter + "&";
				if("expand" in testObject) url = url + "$expand=" + testObject.expand + "&";
				if("orderby" in testObject) url = url + "$orderby=" + testObject.orderby + "&";
				if("select" in testObject) url = url + "$select=" + testObject.select + "&";
				return url;
			}
			
			function traverse(o) {
				var obj = [];
				for (var i in o) {
					var rvalue = {};
					rvalue.text = i;
					if (o[i] !== null && typeof(o[i])=="object") {
						rvalue.children = (traverse(o[i]));
					} else {
						rvalue.text = i + " : " + o[i]; 	
					}
					obj.push(rvalue);
				}
				return obj;
			}

			function refreshCallback(testObject) {
				createTreeView(testObject);
				viewModel.tests()[testObject.id] = testObject;
			}

			function refreshTest(testObject) {
				var data = viewModel.tests()[testObject.id];
				getData(data, refreshCallback);
			}
			
			function getData(testObject, callBack) {
				$.ajax({
					  type: "GET",
					  url: testObject.query
					})
					.done(function(response, textStatus, jqXHR) {
						testObject.resultCode = jqXHR.status;
						testObject.responseText = jqXHR.responseText;
					})
					.fail(function(jqXHR, textStatus, errorThrown) {
						testObject.resultCode = jqXHR.status;
						testObject.responseText = jqXHR.responseText;
					})
					.always(function (a, textStatus, b) {
						testObject.result = textStatus;
						callBack(testObject);		
					});
			}
			
			function createAccordion(testObject) {
				$accordion = $("#accordion_" + testObject.id );
				$accordion.accordion({
					heightStyle: "content",
					collapsible: true,
					active: false,
					create: function (event, ui) {
						$accordion.children('h3').each(function (i) {
							$(this).before('<a class="accordion-link link refresh" id="test_'+testObject.id+'"></a>');
							$(this).before('<a class="accordion-link link ' + testObject.result + '"></a>');
						});
						
						$accordion.find('.refresh').click(function () {
							$accordion.accordion( "option", "active", $(this).data('index') );
							refreshTest(testObject);
							// return false;
						});
					}
				});				
			}
			
			function createTreeView(testObject) {
				if(testObject.resultCode == 200) {
					var result = traverse(JSON.parse(testObject.responseText))
					$('#treeview_' + testObject.id).jstree({
						'core' : {
							data : result
							}
						});
				}				
			}
			
			function testCallback(testObject) {
				viewModel.addTest(testObject);
				createAccordion(testObject);
				createTreeView(testObject);
				runAllTests(testObject.id + 1);
			}
			
			function runAllTests(counter) {

				if(counter < testArray.length) {
					var testUrl = buildUrl(testArray[counter]);
					var testObject = { 	id: counter,
										service: testArray[counter].service,
										resource: testArray[counter].resource,
										query: testUrl,
										result: "",
										resultCode: "",
										responseText: ""
									};
					
					getData(testObject, testCallback);
					
				} else if (counter == testArray.length) {
					$("#runTests").attr("disabled", false);
				}
			}
			
			$("#runTests").click(function () {
				viewModel.removeTests();
				runAllTests(0);
				$(this).attr("disabled", true);
			});        

		});
        </script>
        
        
    </head>
    <body>
		<div>
			<button id="runTests">Run Tests</button>

		<div data-bind='visible: tests().length > 0'>
			<div data-bind='foreach: tests'>
				<div data-bind="attr: { id : 'accordion_' + $index() }">
					<h3 data-bind="text: service + '/' + resource"></h3>
					<div>
						<table>
							<tr>
								<td>Query: <span data-bind="text:query"></span></td>
								<td>Result Code: <span data-bind="text:resultCode"></span></td>
							</tr>
						</table>
						<fieldset>
							<legend>Result: </legend>
							<div data-bind="attr: { id : 'treeview_' + $index() }"></div>
							<!-- ko ifnot: resultCode == 200 -->
							<div data-bind="html: responseText"></div>
							<!-- /ko -->							
						</fieldset>
					</div>
				</div>
			</div>
		</div>

	</div>
		
		
	</body>
</html>
