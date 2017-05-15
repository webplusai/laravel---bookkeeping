function CommonFunc() {

	var output = {};

	output.barChartOptions = {
        legend: { position: 'none' },
        bar: { groupWidth: '100%' },
        isStacked: true,
        colors: ['#BFAEEA','#A68EE1','#542FB1'],
        chartArea: {left: '0%',top: '20%',width: '100%',height: 50 },
        hAxis: {textPosition: 'none',gridlines:{color: 'transparent' },minValue: 0},
        vAxis: {textPosition: 'none',gridlines:{count: 0,color: 'transparent'},minValue: 0 },
        baselineColor: '#fff',
        width: '100%',
        height: '100%'
	}

	output.columnChartOptions = {
	    title:"Yearly Coffee Consumption by Country",
	    height:250,
	    legend: {position: "none",},
	    vAxis: {title: ""},isStacked: true,
	    hAxis: {title: ""},
	    colors: ['#542FB1','#BFAEEA'],
	    chartArea: {left: "10%",top: "3%",height: "80%",width: "90%"},
	}

	output.comboChartOptions = {
    	title : 'Monthly Coffee Production by Country',
        seriesType: 'bars',
        series: {5: {type: 'line'}},
        colors: ['#BFAEEA','#e9e9e9','#FF847C','#E84A5F','#474747'],
        height: 250,
        fontSize: 12,
        chartArea: { left: '10%',width: '90%',top: '3%',height: '80%' },
        vAxis: { title: '',gridlines:{ color: '#e9e9e9',count: 5 },minValue: 0 },
        hAxis: { title: '',gridlines:{ color: '#e9e9e9',count: 5 },minValue: 0 },
	}

	output.pieChartOptions_Dashboard = {
		height: 200,
	  	colors: ['#734ED0','#542FB1','#A68EE1','#BFAEEA','#8067AF'],
	  	legend: {position:'left'},
	  	pieHole: 0.4,
	  	chartArea: {left: "10%",top: "3%",height: "80%",width: "100%"},
	  	animation: {duration:800,easing:'in'}
	}

	output.pieChartOptions_Snapshot = {
        title: '',
        height: 400,
        fontSize: 12,
        colors:['#734ED0','#542FB1','#A68EE1','#BFAEEA','#8067AF'],
        chartArea: { left: '5%',width: '90%',height: 350 },
    };

    output.actionRow = function(row, tableName) {
		var rowAction = '';
		if (row.is_active == 0) {
            rowAction += '<button class="btn btn-secondary" data-ng-click="updatePerson(' + row.id + ',{is_active: 1,tableName: \'' + tableName + '\'})"> <span style="padding: 32px;"> Make Active </span> </button>';
        } else if (row.is_active == 1) {
            rowAction += '<div class="dropdown">';
            rowAction += '	<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"> Create Sales Receipt </button>';
            rowAction += '	<div class="dropdown-menu">';
            rowAction += '		<a class="dropdown-item action-item" data-ng-click="goToNewSalesReceipt(\'' + row.name + '\')"> Create Sales Receipt </a> ';
            if (tableName == 'customer') {
            	rowAction += '		<a class="dropdown-item action-item" data-ng-click="goToNewInvoice(\'' + row.name + '\')"> Create Invoice </a>';
            	rowAction += '		<a class="dropdown-item action-item" data-ng-click="goToNewPayment(\'' + row.name + '\')"> Receive Payment </a>';
            }
            rowAction += '		<a class="dropdown-item action-item" data-ng-click="goToNewExpense(\'' + row.name + '\')"> Create Expense </a>';
            rowAction += '		<a class="dropdown-item action-item" data-ng-click="showEditPersonDialog(' + row.id + ')"> Edit </a>';
            rowAction += '		<a class="dropdown-item action-item" data-ng-click="updatePerson(' + row.id + ',{is_active: 0,tableName: \'' + tableName + '\'})"> Make Inactive </a>';
            rowAction += '	</div>';
            rowAction += '</div>';
        }

        return rowAction;
	}

	output.appendRowToDataTable = function(dataTable,row) {
		dataTable.row.add(row);
		dataTable.draw();
	}

	output.chartOfAccountActionRow = function( row, tableName ) {
		var rowAction 	=	'<div class="dropdown">';
			rowAction 	+=	'	<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"> Edit </button>';
			rowAction 	+=	'	<div class="dropdown-menu">';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="showEdit' + tableName + 'Dialog(' + row.id + ')"> Edit </a>';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="showDeleteDialog(' + row.id + ')"> Delete </a>';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="goToAccountHistory(' + row.id + ')"> Account History </a>';
			rowAction 	+=	'	</div>';
			rowAction 	+=	'</div>';

		return rowAction;
	}

	output.cloneObject = function ( srcObject ) {
		return JSON.parse( JSON.stringify( srcObject ) );
	}

	output.editDeleteActionRow = function(row, tableName) {

		var rowAction 	=	'<div class="dropdown">';
			rowAction 	+=	'	<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"> Edit </button>';
			rowAction 	+=	'	<div class="dropdown-menu">';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="showEdit' + tableName + 'Dialog(' + row.id + ')"> Edit </a>';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="showDeleteDialog(' + row.id + ')"> Delete </a>';
			rowAction 	+=	'	</div>';
			rowAction 	+=	'</div>';

		return rowAction;
	}

	output.drawBarChart = function(chartId,data,options) {	
      	var chart = new google.visualization.BarChart(document.getElementById(chartId));
      	chart.draw(data,options);
	}

	output.drawPieChart = function(chartId,data,options) {
		var chart = new google.visualization.PieChart(document.getElementById(chartId));
		chart.draw(data,options);
	}

	output.drawColumnChart = function(chartId,data,options) {
		var chart = new google.visualization.ColumnChart(document.getElementById(chartId));
    	chart.draw(data,options);
	}

	output.drawComboChart = function(chartId,data,options) {
		var bar = new google.visualization.ComboChart(document.getElementById(chartId));
        bar.draw(data,options);
	}

	output.showPreloader = function( selector ) {
		$( selector ).css( 'opacity',1 );
		$( selector ).show();
	}

	output.hidePreloader = function( selector ) {
		$( selector ).animate( { opacity: "0" },500,function() {
			$( selector ).hide();
		} );
	}

	output.goToTransaction = function( transactionId, transactionType, $state ) {
		var states = $state.current.name.split('.');
		$state.go( states[0] + '.' + states[1] + '.' + states[2] + '.' + transactionType.toLowerCase(),{ trxnId: transactionId } );
	}

	output.initializeCheckBox = function(checkBoxSelector) {    // Used in Login page
		if($(checkBoxSelector).length){
			$(checkBoxSelector).iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue',
			});
		}
	}

	output.initializeCheckRadioBox = function(checkBoxClass) {   // User in Balance sheet page
		$('.colors li').on('click',function() {
	      	var self = $(this);
	      	if (!self.hasClass('active')) {
	        	self.siblings().removeClass('active');

		        var skin = self.closest('.skin'),
		          	color = self.attr('class') ? '-' + self.attr('class') : '',
		          	checkbox = skin.data('icheckbox'),
		          	radio = skin.data('iradio'),
		          	checkbox_default = 'icheckbox_minimal',
		          	radio_default = 'iradio_minimal';

		        if (skin.hasClass(checkBoxClass)) {
		          	checkbox_default = 'icheckbox_square';
		          	radio_default = 'iradio_square';
		          	checkbox === undefined && (checkbox = 'icheckbox_square-red',radio = 'iradio_square-red');
		        }

		        checkbox === undefined && (checkbox = checkbox_default,radio = radio_default);

		        skin.find('input,.skin-states .state').each(function() {
		          	var element = $(this).hasClass('state') ? $(this) : $(this).parent(),
		            	element_class = element.attr('class').replace(checkbox,checkbox_default + color).replace(radio,radio_default + color);
		          		element.attr('class',element_class);
		        });

		        skin.data('icheckbox',checkbox_default + color);
		        skin.data('iradio',radio_default + color);
		        self.addClass('active');
	      	}
	    });

	    $('.' + checkBoxClass + ' input').iCheck({
	        checkboxClass: 'icheckbox_square-red',
	        radioClass: 'iradio_square-red',
	    });
	}

	output.initializeDataTable = function( tableSelector ,columns, scope, compile, bServerSide, serverEndPoint, modifyDataCallback ) {
		var aoColumns = [];
		var columnDefs = [];
		for (i = 0; i < columns.length; i++) {
			aoColumns.push( { sTitle: columns[i], bSortable: true } );

			if ( $( tableSelector ).hasClass( 'table-customers' ) || $( tableSelector ).hasClass( 'table-suppliers' ) ) {
				var responsivePriority = 1;
				if ( i == 5 )
					responsivePriority = 10000;
				columnDefs.push( { targets: i, responsivePriority: responsivePriority } );
			}
		}

		var tableData = {
			oLanguage: {
				sSearch: "Filter"
			},
			dom: 'Blfrtip',
	        buttons: [
	            'copy','csv','excel','pdf','print'
	        ],
			sPaginationType: "full_numbers",
			aoColumns: aoColumns,
			fnCreatedRow: function( nRow,aData,iDataIndex ) {
				compile(nRow)(scope);
			},
			colReorder: true,
	        responsive: true,
	        columnDefs: [
		        { responsivePriority: 1, targets: 0 },
		        { responsivePriority: 2, targets: -1 }
		    ],
	        autoWidth: true
		};

		if ( bServerSide ) {
			// tableData.processing = true;
			tableData.serverSide = true;
			tableData.deferLoading = 0;
			tableData.ajax = {
				type: 'get',
				url: 'saudisms/whm/api' + serverEndPoint,
				headers: {
					Authorization: 'Bearer ' + sessionStorage.access_token
				},
				dataSrc: 'data',
				dataFilter: function( data ){
					return modifyDataCallback( data );
		        }
			};
		}

		$(".icon-print").click(function(event) {
			event.stopPropagation();
			$(".buttons-print").trigger("click");
		});

		$(".icon-file-excel-o").click(function(event) {
			event.stopPropagation();
			$(".buttons-excel").trigger("click");
		});

		$(".icon-pencil3").click(function(event) {
			event.stopPropagation();
			event.preventDefault();
		});

		$("ul.list-inline li,ul.list-inline li a").click(function(event) {
			event.preventDefault();
			event.stopPropagation();
		});

		var dataTable = $(tableSelector).DataTable(tableData);

		return dataTable;
	}

	output.initializeDatePicker = function(datePickerSelector,scope,defaultDate) {
		$( datePickerSelector ).datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'yy-mm-dd'
		});

		if ( defaultDate ) {
			setTimeout( function() {
				$( datePickerSelector ).datepicker( 'setDate',defaultDate );
				$( datePickerSelector ).trigger( 'change' );
			},1000);
		}

		$( datePickerSelector ).keydown(function(event) {
			event.preventDefault();
		});

		$('.ui-datepicker').wrap('<div class="dp-skin"/>');
	}

	output.initializeDropzone = function(dropzoneSelector) {
		$(dropzoneSelector).dropzone({ url: "/" });
	}

	output.initializeCountryDropdown = function( dropdownSelector ) {
		$( dropdownSelector ).select2( { data: countryList } );
	}

	output.initializeClickAndHide = function(selector) {
		$(document).mouseup(function (e) {
	      	var container = $(selector);

	      	if (!container.is(e.target) && container.has(e.target).length === 0) {
	        	container.hide();
	      	}
	    });
	}

	output.initializeSelect = function(selectSelector) {

		var $select = $(selectSelector).selectize({
			create: true,
			sortField: {
				field: 'text',
				direction: 'asc'
			},
			dropdownParent: 'body'
		});
		
	    $('#select-beast-selectized').trigger(jQuery.Event('keydown',{keyCode: 8}));
	}

	output.initializeSwitch = function(switchSelector) {
		var $html = $('html');

	    $('.switch:checkbox').checkboxpicker();
	    $(".switchBootstrap").bootstrapSwitch();

	    var elems = $(switchSelector);
	    $.each( elems,function( key,value ) {
	        var switchery = new Switchery($(this)[0],{ className: "switchery",color: "#967ADC" });
	    });
	}

	output.initializeValidation = function( formSelector,onSuccess ) {
		if ( $( formSelector ).attr( "novalidate" ) != undefined ) {
			$( formSelector ).find( "input,select,textarea" ).not( "[type=submit]" ).jqBootstrapValidation( {
				preventSubmit: true,
		        submitSuccess: onSuccess
			} );
		}
	}

	output.productServiceActionRow = function(row) {

		var rowAction 	=	'';
		if (row.is_active == 0) {
			rowAction 	+=	'<button class="btn btn-secondary" href="" data-ng-click="updateProductService(' + row.id + ',{is_active: 1,tableName: \'product_service\'})">  Make Active </button>';
		} else {
			rowAction 	+=	'<div class="dropdown">';
			rowAction 	+=	'	<button class="btn btn-secondary dropdown-toggle" type="button" data-toggle="dropdown"> Edit </button>';
			rowAction 	+=	'	<div class="dropdown-menu">';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="showEditProductServiceDialog(' + row.id + ')"> Edit </a>';
			rowAction 	+=	'		<a class="dropdown-item" href="" data-ng-click="updateProductService(' + row.id + ',{is_active: 0,tableName:\'product_service\'})"> Make Inactive </a>';
			rowAction   += 	'		<a class="dropdown-item" href="" data-ng-click="duplicateProductService(' + row.id + ')"> Duplicate </a>';
			rowAction 	+=	'	</div>';
			rowAction 	+=	'</div>';
		}
			
		return rowAction;
	}

	output.redrawDataTable = function(dataTable, data, rowCallback, tableName) {
		dataTable.clear().draw();
		for (var i = 0; i < data.length; i++) {
			dataTable.row.add(rowCallback(data[i],i,tableName));
		}
		dataTable.draw();
		// if ( tableName == 'sales' || tableName == 'expense' )
		// 	dataTable.columns.adjust().order( [ 1,'desc' ] ).draw();
		// else
		// 	dataTable.columns.adjust().order( [ 1,'asc' ] ).draw();
	}

	output.setPayeeTypeId = function(payeeType,payeeId) {
		localStorage.setItem('payeeId',payeeId);
		localStorage.setItem('payeeType',payeeType);
	}

	output.setPreloadWatcher = function( $scope,value ) {
		$scope.$watch( function() {
			return $scope.preloadCounter;
		},function( newVal,oldVal ) {
			if ( newVal == value )
				output.hidePreloader( '.full-screen-loader' );
		} );
	}

	output.toggle = function(className,ele)
	{
		if($("."+className).css("display") === "none")
		{
		  $(ele).removeClass('icon-caret-up').addClass('icon-caret-down')
		}
		else
		{
		  $(ele).removeClass('icon-caret-down').addClass('icon-caret-up')
		}
		$("."+className).slideToggle("slow");
	}

	output.toggleEditHeader = function(id,id2) {
	   $(id).toggle();
	   $(id2).toggle();
	}

	output.toggleEditNotes = function(id,id2) {
	    $(id).toggle();
	    $(id2).toggle();
	    //$(id).toggle(".addEditNotes").delay(500).fadeTo();
	    $('html,body').animate({
	      scrollTop: $("#addEditNotes").offset().top
	    },1000)
	}

	output.exportToExcel = function( table, workSheetName, fileName ) {
		var uri = 'data:application/vnd.ms-excel;base64,'
        , template = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'
        , base64 = function ( s ) { return window.btoa( unescape( encodeURIComponent( s ) ) ) }
        , format = function ( s, c ) { return s.replace( /{(\w+)}/g, function ( m, p ) { return c[p]; } ) }

        if ( !table.nodeType ) table = document.getElementById( table );
        var ctx = { worksheet: workSheetName || 'Worksheet', table: table.innerHTML }

        document.getElementById( "excelExport" ).href = uri + base64( format( template, ctx ) );
        document.getElementById( "excelExport" ).download = fileName;
	}

	output.exportToPDF = function( contentSelector, fileName ) {
		$( '#pdfContent input' ).val( $( '.pdf-content' ).html() );
		$( '#pdfContent' ).submit();
	}

	var countryList = [
      { "id": "Afghanistan",                                   "text": "Afghanistan",                                    },           
      { "id": "Åland Islands",                                 "text": "Åland Islands",                                  },           
      { "id": "Albania",                                       "text": "Albania",                                        },           
      { "id": "Algeria",                                       "text": "Algeria",                                        },           
      { "id": "American Samoa",                                "text": "American Samoa",                                 },           
      { "id": "Andorra",                                       "text": "Andorra",                                        },           
      { "id": "Angola",                                        "text": "Angola",                                         },           
      { "id": "Anguilla",                                      "text": "Anguilla",                                       },           
      { "id": "Antarctica",                                    "text": "Antarctica",                                     },           
      { "id": "Antigua and Barbuda",                           "text": "Antigua and Barbuda",                            },           
      { "id": "Argentina",                                     "text": "Argentina",                                      },           
      { "id": "Armenia",                                       "text": "Armenia",                                        },           
      { "id": "Aruba",                                         "text": "Aruba",                                          },           
      { "id": "Australia",                                     "text": "Australia",                                      },           
      { "id": "Austria",                                       "text": "Austria",                                        },           
      { "id": "Azerbaijan",                                    "text": "Azerbaijan",                                     },           
      { "id": "Bahamas",                                       "text": "Bahamas",                                        },           
      { "id": "Bahrain",                                       "text": "Bahrain",                                        },           
      { "id": "Bangladesh",                                    "text": "Bangladesh",                                     },           
      { "id": "Barbados",                                      "text": "Barbados",                                       },           
      { "id": "Belarus",                                       "text": "Belarus",                                        },           
      { "id": "Belgium",                                       "text": "Belgium",                                        },           
      { "id": "Belize",                                        "text": "Belize",                                         },           
      { "id": "Benin",                                         "text": "Benin",                                          },           
      { "id": "Bermuda",                                       "text": "Bermuda",                                        },           
      { "id": "Bhutan",                                        "text": "Bhutan",                                         },           
      { "id": "Bolivia",                                       "text": "Bolivia",                                        },           
      { "id": "Bonaire, Sint Eustatius and Saba",              "text": "Bonaire, Sint Eustatius and Saba",               },           
      { "id": "Bosnia and Herzegovina",                        "text": "Bosnia and Herzegovina",                         },           
      { "id": "Botswana",                                      "text": "Botswana",                                       },           
      { "id": "Bouvet Island",                                 "text": "Bouvet Island",                                  },           
      { "id": "Brazil",                                        "text": "Brazil",                                         },           
      { "id": "British Indian Ocean Territory",                "text": "British Indian Ocean Territory",                 },           
      { "id": "Brunei Darussalam",                             "text": "Brunei Darussalam",                              },           
      { "id": "Bulgaria",                                      "text": "Bulgaria",                                       },           
      { "id": "Burkina Faso",                                  "text": "Burkina Faso",                                   },           
      { "id": "Burundi",                                       "text": "Burundi",                                        },           
      { "id": "Cambodia",                                      "text": "Cambodia",                                       },           
      { "id": "Cameroon",                                      "text": "Cameroon",                                       },           
      { "id": "Canada",                                        "text": "Canada",                                         },           
      { "id": "Cape Verde",                                    "text": "Cape Verde",                                     },           
      { "id": "Cayman Islands",                                "text": "Cayman Islands",                                 },           
      { "id": "Central African Republic",                      "text": "Central African Republic",                       },           
      { "id": "Chad",                                          "text": "Chad",                                           },           
      { "id": "Chile",                                         "text": "Chile",                                          },           
      { "id": "China",                                         "text": "China",                                          },           
      { "id": "Christmas Island",                              "text": "Christmas Island",                               },           
      { "id": "Cocos (Keeling) Islands",                       "text": "Cocos (Keeling) Islands",                        },           
      { "id": "Colombia",                                      "text": "Colombia",                                       },           
      { "id": "Comoros",                                       "text": "Comoros",                                        },           
      { "id": "Congo",                                         "text": "Congo",                                          },           
      { "id": "Congo, The Democratic Republic of the",         "text": "Congo, The Democratic Republic of the",          },
      { "id": "Cook Islands",                                  "text": "Cook Islands",                                   },           
      { "id": "Costa Rica",                                    "text": "Costa Rica",                                     },           
      { "id": "Côte d'Ivoire",                                 "text": "Côte d'Ivoire",                                  },           
      { "id": "Croatia",                                       "text": "Croatia",                                        },           
      { "id": "Cuba",                                          "text": "Cuba",                                           },           
      { "id": "Curaçao",                                       "text": "Curaçao",                                        },           
      { "id": "Cyprus",                                        "text": "Cyprus",                                         },           
      { "id": "Czech Republic",                                "text": "Czech Republic",                                 },           
      { "id": "Denmark",                                       "text": "Denmark",                                        },           
      { "id": "Djibouti",                                      "text": "Djibouti",                                       },           
      { "id": "Dominica",                                      "text": "Dominica",                                       },           
      { "id": "Dominican Republic",                            "text": "Dominican Republic",                             },           
      { "id": "Ecuador",                                       "text": "Ecuador",                                        },           
      { "id": "Egypt",                                         "text": "Egypt",                                          },           
      { "id": "El Salvador",                                   "text": "El Salvador",                                    },           
      { "id": "Equatorial Guinea",                             "text": "Equatorial Guinea",                              },           
      { "id": "Eritrea",                                       "text": "Eritrea",                                        },           
      { "id": "Estonia",                                       "text": "Estonia",                                        },           
      { "id": "Ethiopia",                                      "text": "Ethiopia",                                       },           
      { "id": "Falkland Islands (Malvinas)",                   "text": "Falkland Islands (Malvinas)",                    },           
      { "id": "Faroe Islands",                                 "text": "Faroe Islands",                                  },           
      { "id": "Fiji",                                          "text": "Fiji",                                           },           
      { "id": "Finland",                                       "text": "Finland",                                        },           
      { "id": "France",                                        "text": "France",                                         },           
      { "id": "French Guiana",                                 "text": "French Guiana",                                  },           
      { "id": "French Polynesia",                              "text": "French Polynesia",                               },           
      { "id": "French Southern Territories",                   "text": "French Southern Territories",                    },           
      { "id": "Gabon",                                         "text": "Gabon",                                          },           
      { "id": "Gambia",                                        "text": "Gambia",                                         },           
      { "id": "Georgia",                                       "text": "Georgia",                                        },           
      { "id": "Germany",                                       "text": "Germany",                                        },           
      { "id": "Ghana",                                         "text": "Ghana",                                          },           
      { "id": "Gibraltar",                                     "text": "Gibraltar",                                      },           
      { "id": "Greece",                                        "text": "Greece",                                         },           
      { "id": "Greenland",                                     "text": "Greenland",                                      },           
      { "id": "Grenada",                                       "text": "Grenada",                                        },           
      { "id": "Guadeloupe",                                    "text": "Guadeloupe",                                     },           
      { "id": "Guam",                                          "text": "Guam",                                           },           
      { "id": "Guatemala",                                     "text": "Guatemala",                                      },           
      { "id": "Guernsey",                                      "text": "Guernsey",                                       },           
      { "id": "Guinea",                                        "text": "Guinea",                                         },           
      { "id": "Guinea-Bissau",                                 "text": "Guinea-Bissau",                                  },           
      { "id": "Guyana",                                        "text": "Guyana",                                         },           
      { "id": "Haiti",                                         "text": "Haiti",                                          },           
      { "id": "Heard Island and McDonald Islands",             "text": "Heard Island and McDonald Islands",              },           
      { "id": "Holy See (Vatican City State)",                 "text": "Holy See (Vatican City State)",                  },           
      { "id": "Honduras",                                      "text": "Honduras",                                       },           
      { "id": "Hong Kong",                                     "text": "Hong Kong",                                     },           
      { "id": "Hungary",                                       "text": "Hungary",                                       },           
      { "id": "Iceland",                                       "text": "Iceland",                                       },           
      { "id": "India",                                         "text": "India",                                         },           
      { "id": "Indonesia",                                     "text": "Indonesia",                                     },           
      { "id": "Iran, Islamic Republic of",                     "text": "Iran, Islamic Republic of",                     },           
      { "id": "Iraq",                                          "text": "Iraq",                                          },           
      { "id": "Ireland",                                       "text": "Ireland",                                       },           
      { "id": "Isle of Man",                                   "text": "Isle of Man",                                   },           
      { "id": "Israel",                                        "text": "Israel",                                        },           
      { "id": "Italy",                                         "text": "Italy",                                         },           
      { "id": "Jamaica",                                       "text": "Jamaica",                                       },           
      { "id": "Japan",                                         "text": "Japan",                                         },           
      { "id": "Jersey",                                        "text": "Jersey",                                        },           
      { "id": "Jordan",                                        "text": "Jordan",                                        },           
      { "id": "Kazakhstan",                                    "text": "Kazakhstan",                                    },           
      { "id": "Kenya",                                         "text": "Kenya",                                         },           
      { "id": "Kiribati",                                      "text": "Kiribati",                                      },           
      { "id": "Korea, Democratic People's Republic of",        "text": "Korea, Democratic People's Republic of",        },
      { "id": "Korea, Republic of",                            "text": "Korea, Republic of",                            },           
      { "id": "Kuwait",                                        "text": "Kuwait",                                        },           
      { "id": "Kyrgyzstan",                                    "text": "Kyrgyzstan",                                    },           
      { "id": "Lao People's Democratic Republic",              "text": "Lao People's Democratic Republic",              },           
      { "id": "Latvia",                                        "text": "Latvia",                                        },           
      { "id": "Lebanon",                                       "text": "Lebanon",                                       },           
      { "id": "Lesotho",                                       "text": "Lesotho",                                       },           
      { "id": "Liberia",                                       "text": "Liberia",                                       },           
      { "id": "Libya",                                         "text": "Libya",                                         },           
      { "id": "Liechtenstein",                                 "text": "Liechtenstein",                                 },           
      { "id": "Lithuania",                                     "text": "Lithuania",                                     },           
      { "id": "Luxembourg",                                    "text": "Luxembourg",                                    },           
      { "id": "Macao",                                         "text": "Macao",                                         },           
      { "id": "Macedonia, Republic Of",                        "text": "Macedonia, Republic Of",                        },           
      { "id": "Madagascar",                                    "text": "Madagascar",                                    },           
      { "id": "Malawi",                                        "text": "Malawi",                                        },           
      { "id": "Malaysia",                                      "text": "Malaysia",                                      },           
      { "id": "Maldives",                                      "text": "Maldives",                                      },           
      { "id": "Mali",                                          "text": "Mali",                                          },           
      { "id": "Malta",                                         "text": "Malta",                                         },           
      { "id": "Marshall Islands",                              "text": "Marshall Islands",                              },           
      { "id": "Martinique",                                    "text": "Martinique",                                    },           
      { "id": "Mauritania",                                    "text": "Mauritania",                                    },           
      { "id": "Mauritius",                                     "text": "Mauritius",                                     },           
      { "id": "Mayotte",                                       "text": "Mayotte",                                       },           
      { "id": "Mexico",                                        "text": "Mexico",                                        },           
      { "id": "Micronesia, Federated States of",               "text": "Micronesia, Federated States of",               },           
      { "id": "Moldova, Republic of",                          "text": "Moldova, Republic of",                          },           
      { "id": "Monaco",                                        "text": "Monaco",                                        },           
      { "id": "Mongolia",                                      "text": "Mongolia",                                      },           
      { "id": "Montenegro",                                    "text": "Montenegro",                                    },           
      { "id": "Montserrat",                                    "text": "Montserrat",                                    },           
      { "id": "Morocco",                                       "text": "Morocco",                                       },           
      { "id": "Mozambique",                                    "text": "Mozambique",                                    },           
      { "id": "Myanmar",                                       "text": "Myanmar",                                       },           
      { "id": "Namibia",                                       "text": "Namibia",                                       },           
      { "id": "Nauru",                                         "text": "Nauru",                                         },           
      { "id": "Nepal",                                         "text": "Nepal",                                         },           
      { "id": "Netherlands",                                   "text": "Netherlands",                                   },           
      { "id": "New Caledonia",                                 "text": "New Caledonia",                                 },           
      { "id": "New Zealand",                                   "text": "New Zealand",                                   },           
      { "id": "Nicaragua",                                     "text": "Nicaragua",                                     },           
      { "id": "Niger",                                         "text": "Niger",                                         },           
      { "id": "Nigeria",                                       "text": "Nigeria",                                       },           
      { "id": "Niue",                                          "text": "Niue",                                          },           
      { "id": "Norfolk Island",                                "text": "Norfolk Island",                                },           
      { "id": "Northern Mariana Islands",                      "text": "Northern Mariana Islands",                      },           
      { "id": "Norway",                                        "text": "Norway",                                        },           
      { "id": "Oman",                                          "text": "Oman",                                          },           
      { "id": "Pakistan",                                      "text": "Pakistan",                                      },           
      { "id": "Palau",                                         "text": "Palau",                                         },           
      { "id": "Palestinian Territory, Occupied",               "text": "Palestinian Territory, Occupied",               },           
      { "id": "Panama",                                        "text": "Panama",                                        },           
      { "id": "Papua New Guinea",                              "text": "Papua New Guinea",                              },           
      { "id": "Paraguay",                                      "text": "Paraguay",                                      },           
      { "id": "Peru",                                          "text": "Peru",                                          },           
      { "id": "Philippines",                                   "text": "Philippines",                                   },           
      { "id": "Pitcairn",                                      "text": "Pitcairn",                                      },           
      { "id": "Poland",                                        "text": "Poland",                                        },           
      { "id": "Portugal",                                      "text": "Portugal",                                      },           
      { "id": "Puerto Rico",                                   "text": "Puerto Rico",                                   },           
      { "id": "Qatar",                                         "text": "Qatar",                                         },           
      { "id": "Reunion",                                       "text": "Reunion",                                       },           
      { "id": "Romania",                                       "text": "Romania",                                       },           
      { "id": "Russian Federation",                            "text": "Russian Federation",                            },           
      { "id": "Rwanda",                                        "text": "Rwanda",                                        },           
      { "id": "Saint Barthélemy",                              "text": "Saint Barthélemy",                              },           
      { "id": "Saint Helena, Ascension and Tristan da Cunha",  "text": "Saint Helena, Ascension and Tristan da Cunha",  },
      { "id": "Saint Kitts and Nevis",                         "text": "Saint Kitts and Nevis",                         },           
      { "id": "Saint Lucia",                                   "text": "Saint Lucia",                                   },           
      { "id": "Saint Martin (French Part)",                    "text": "Saint Martin (French Part)",                    },           
      { "id": "Saint Pierre and Miquelon",                     "text": "Saint Pierre and Miquelon",                     },           
      { "id": "Saint Vincent and the Grenadines",              "text": "Saint Vincent and the Grenadines",              },           
      { "id": "Samoa",                                         "text": "Samoa",                                         },           
      { "id": "San Marino",                                    "text": "San Marino",                                    },           
      { "id": "Sao Tome and Principe",                         "text": "Sao Tome and Principe",                         },           
      { "id": "Saudi Arabia",                                  "text": "Saudi Arabia",                                  },           
      { "id": "Senegal",                                       "text": "Senegal",                                       },           
      { "id": "Serbia",                                        "text": "Serbia",                                        },           
      { "id": "Seychelles",                                    "text": "Seychelles",                                    },           
      { "id": "Sierra Leone",                                  "text": "Sierra Leone",                                  },           
      { "id": "Singapore",                                     "text": "Singapore",                                     },           
      { "id": "Sint Maarten (Dutch Part)",                     "text": "Sint Maarten (Dutch Part)",                     },           
      { "id": "Slovakia",                                      "text": "Slovakia",                                      },           
      { "id": "Slovenia",                                      "text": "Slovenia",                                      },           
      { "id": "Solomon Islands",                               "text": "Solomon Islands",                               },           
      { "id": "Somalia",                                       "text": "Somalia",                                       },           
      { "id": "South Africa",                                  "text": "South Africa",                                  },           
      { "id": "South Georgia and the South Sandwich Islands",  "text": "South Georgia and the South Sandwich Islands",  },
      { "id": "South Sudan",                                   "text": "South Sudan",                                   },           
      { "id": "Spain",                                         "text": "Spain",                                         },           
      { "id": "Sri Lanka",                                     "text": "Sri Lanka",                                     },           
      { "id": "Sudan",                                         "text": "Sudan",                                         },           
      { "id": "Suriname",                                      "text": "Suriname",                                      },           
      { "id": "Svalbard and Jan Mayen",                        "text": "Svalbard and Jan Mayen",                        },           
      { "id": "Swaziland",                                     "text": "Swaziland",                                     },           
      { "id": "Sweden",                                        "text": "Sweden",                                        },           
      { "id": "Switzerland",                                   "text": "Switzerland",                                   },           
      { "id": "Syrian Arab Republic",                          "text": "Syrian Arab Republic",                          },           
      { "id": "Taiwan",                                        "text": "Taiwan",                                        },           
      { "id": "Tajikistan",                                    "text": "Tajikistan",                                    },           
      { "id": "Tanzania, United Republic of",                  "text": "Tanzania, United Republic of",                  },           
      { "id": "Thailand",                                      "text": "Thailand",                                      },           
      { "id": "Timor-Leste",                                   "text": "Timor-Leste",                                   },           
      { "id": "Togo",                                          "text": "Togo",                                          },           
      { "id": "Tokelau",                                       "text": "Tokelau",                                       },           
      { "id": "Tonga",                                         "text": "Tonga",                                         },           
      { "id": "Trinidad and Tobago",                           "text": "Trinidad and Tobago",                           },           
      { "id": "Tunisia",                                       "text": "Tunisia",                                       },           
      { "id": "Turkey",                                        "text": "Turkey",                                        },           
      { "id": "Turkmenistan",                                  "text": "Turkmenistan",                                  },           
      { "id": "Turks and Caicos Islands",                      "text": "Turks and Caicos Islands",                      },           
      { "id": "Tuvalu",                                        "text": "Tuvalu",                                        },           
      { "id": "Uganda",                                        "text": "Uganda",                                        },           
      { "id": "Ukraine",                                       "text": "Ukraine",                                       },           
      { "id": "United Arab Emirates",                          "text": "United Arab Emirates",                          },           
      { "id": "United Kingdom",                                "text": "United Kingdom",                                },           
      { "id": "United States",                                 "text": "United States",                                 },           
      { "id": "United States Minor Outlying Islands",          "text": "United States Minor Outlying Islands",          },           
      { "id": "Uruguay",                                       "text": "Uruguay",                                       },           
      { "id": "Uzbekistan",                                    "text": "Uzbekistan",                                    },           
      { "id": "Vanuatu",                                       "text": "Vanuatu",                                       },           
      { "id": "Venezuela",                                     "text": "Venezuela",                                     },           
      { "id": "Viet Nam",                                      "text": "Viet Nam",                                      },           
      { "id": "Virgin Islands, British",                       "text": "Virgin Islands, British",                       },           
      { "id": "Virgin Islands, U.S.",                          "text": "Virgin Islands, U.S.",                          },           
      { "id": "Wallis and Futuna",                             "text": "Wallis and Futuna",                             },           
      { "id": "Western Sahara",                                "text": "Western Sahara",                                },           
      { "id": "Yemen",                                         "text": "Yemen",                                         },           
      { "id": "Zambia",                                        "text": "Zambia",                                        },           
      { "id": "Zimbabwe",                                      "text": "Zimbabwe",                                      }           
    ];

	return output;

}