
<form method="POST" action="<?php echo site_url($_directory . 'controls/Advance_search_filter'); ?>" class="bulk-receipt-download-form" id="myForm">
<div class="advanceSearchRow row">
  <div class="advanceFunctionContent">
    <div class="dateContent">
        <div class="bulk-date-field">
          <label>From Date</label>
          <input type="date" name="from_date" class="form-control">
        </div>
        <br>
        <div class="bulk-date-field">
          <label>To Date</label>
          <input type="date" name="to_date" class="form-control">
        </div>
    
    </div>
    <div class="detailContent" style="margin-top: -1px;">
  
  <div class="form-group adv-search-form">
    <div class="row" data-attr='1'>
      <div class="values">
        <label for="values">Values</label>
        <select name="search_category[]" class="form-control search-category">
          <option value="">Select an option</option>
          <?php
          $options = $categories['select_options'];
          foreach ($options as $value => $name) {
            echo '<option value="' . $value . '">' . $name . '</option>';
          }
          ?>
        </select>
        <div class="invalid-feedback1" style="display: none;">Please select an option.</div>
      </div>
      <div class="parameter">
        <div class="inputPara">
          <label for="values">Search Parameter</label>
          <input type="text" name="search_value0[]" class="form-control search-input" placeholder="Search">
          <div class="checkbox-container" style="display: none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" value="donate" name="search_value0[]">
              <label class="form-check-label" for="checkbox1">
                Donate
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="events" name="search_value0[]">
              <label class="form-check-label" for="checkbox2">
                Events
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="conferences" name="search_value0[]">
              <label class="form-check-label" for="checkbox3">
                Conferences
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="short_conferences" name="search_value0[]">
              <label class="form-check-label" for="checkbox3">
                Short Conferences
              </label>
            </div>
          </div>


         <div class="checkbox-container-payment" style="display: none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" value="paypal" name="search_value0[]">
              <label class="form-check-label" for="checkbox1">
                Paypal
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="payeezy" name="search_value0
              []">
              <label class="form-check-label" for="checkbox2">
                Payeezy
              </label>
            </div>
          </div>
        </div>
        <div class="input-group-append">
          <button class="btn btn-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button>
        </div>
        <div class="invalid-feedback" style="display: none;">This field is required.</div>
      </div>
    </div>
  </div>
  <button class="btn btn-primary">Apply</button>
</form>
    </div>
  </div>
     <br>
  <div class="advanceSearchReportTable">
    <div class="spinner-border">
          <i class="busy"></i>
        </div>
      <!-- Download DropDown -->
      <div class="wrapper-dropdown " id="myDropdown" style="display: none;">
        <span>Download</span>
        <ul class="dropdown">
          <li id="downloadCSV" class="download_csv"  data-value="csvdownload">Download CSV</li>
<!--           <li id="downloadPaypalCSV" class="download_csv"  data-value="paypalcsvdownload"> Download Paypal CSV</li>
          <li id="downloadPayeezyCSV" class="download_csv"  data-value="payeezycsvdownload"> Download Payeezy CSV</li> -->
        </ul>
      </div>
      <div class="table-responsive adv-search-table">


        <table style="display: none;" id="dtBasicExample" class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
       <div class="spinner-grow text-primary loader" style="display: none;"></div>
        <thead id="tableHead" >
        <tr>
          <th>Card Holder's Name</th>
          <th>Full Name</th>
          <th>Amount</th>
          <th>Date</th>
          <th>Purpose</th>
          <th>Payment Method</th>
          <th>Payment Mode</th>
          <th>Country Name</th>
          <th>Email</th>
          <th>Status</th>
          <th>Tax Receipt</th>
          <th>Automated Reconciliation Status</th>
        </tr>
        </thead>
        <tbody id="tableBody">
    
        </tbody>
        </table>
      </div>
    </div>

    
<script>

  var formData = '';


  $(document).ready(function() {
    $('.download_csv').on('click', function () {
       var csv_type = $(this).attr('data-value');
      DownloadCsv(csv_type);

    });
    // $('#downloadCSV').click(function() {
    //   DownloadCsv();
    // });
      $('#dtBasicExample').DataTable();
      $('.dataTables_length').addClass('bs-select');
      $(function() {
        var dd1 = new dropDown($('#myDropdown'));
        
        $(document).click(function() {
          $('.wrapper-dropdown').removeClass('active');
        });
      });

      function dropDown(el) {
        this.dd = el;
        this.placeholder = this.dd.children('span');
        this.opts = this.dd.find('ul.dropdown > li');
        this.val = '';
        this.index = -1;
        this.initEvents();
      }
      dropDown.prototype = {
        initEvents: function() {
          var obj = this;
          
          obj.dd.on('click', function() {
            $(this).toggleClass('active');
            return false;
          });
          
          obj.opts.on('click', function() {
            var opt = $(this);
            obj.val = opt.text();
            obj.index = opt.index();
            obj.placeholder.text(obj.val);
          });
        }
      }
    
    // Add Row Button Click Event
 var i = 1;
 $(document).on('click', '.add-row-btn', function() {
  var newRow = $('<div class="row" data-attr="'+i+'">' +
    '<div class="values">' +
    '<label for="values">Values</label>' +
    '<select name="search_category[]" class="form-control search-category" >' +
    '<?php
      $options = $categories['tr_heading'];
      echo '<option value="">Select an option</option>';
      $options = $categories['select_options'];
      foreach ($options as $value => $name) {
        echo '<option value="' . $value . '">' . $name . '</option>';
      }
    ?>' +
    '</select>' +
    '<div class="invalid-feedback1" style="display: none;">Please select an option.</div>' +
    '</div>' +
    '<div class="parameter">' +
    '<div class="inputPara">' +
    '<label for="values">Search Parameter</label>' +
    '<input type="text" class="form-control search-input" name="search_value'+i+'[]" placeholder="Search">' +
    '<div class="checkbox-container" style="display: none;">' +
    '<div class="form-check">' +
    '<input class="form-check-input" type="radio" value="donate" name="search_value'+i+'[]">' +
    '<label class="form-check-label" for="donate">Donate</label>' +
    '</div>' +
    '<div class="form-check">' +
    '<input class="form-check-input" type="radio" value="events" name="search_value'+i+'[]">' +
    '<label class="form-check-label" for="events">Events</label>' +
    '</div>' +
    '<div class="form-check">' +
    '<input class="form-check-input" type="radio" value="conferences" name="search_value'+i+'[]">' +
    '<label class="form-check-label" for="conferences">Conferences</label>' +
    '</div>' +
     '<div class="form-check">' +
    '<input class="form-check-input" type="radio" value="short_conferences" name="search_value'+i+'[]">' +
    '<label class="form-check-label" for="conferences">Short Conferences</label>' +
    '</div>' +
    '</div>' +
     `<div class="checkbox-container-payment" style="display: none;">
            <div class="form-check">
              <input class="form-check-input" type="radio" value="paypal" name="search_value`+i+`[]">
              <label class="form-check-label" for="paypal">
                Paypal
              </label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" value="payeezy" name="search_value`+i+`[]">
              <label class="form-check-label" for="payeezy">
                Payeezy
              </label>
            </div>
          </div>`+
    '</div>' +
    '<div class="input-group-append">' +
    '<button class="btn btn-primary add-row-btn" type="button"><i class="fa fa-plus"></i></button>' +
    '</div>' +
    '<div class="invalid-feedback" style="display: none;">This field is required.</div>' +
    '</div>' +
    '</div>');

  // Change plus icon to minus icon for the previous "Add Row" button
    $('.add-row-btn:last').html('<i class="fa fa-minus"></i>');
    $(this).attr('class', 'btn btn-primary remove-row-btn')

    // Append the new row
    $(this).closest('.row').parent().append(newRow);

    // Add a margin to the new row
    $('.row:last').css('margin-top', '17px');

    // Update the "Add Row" button to plus icon
    $('.add-row-btn:last').html('<i class="fa fa-plus"></i>');
    i++;
});


// Remove Row Button Click Event
$(document).on('click', '.remove-row-btn', function() {
  $(this).closest('.row').remove();
});

// Show/hide input and checkbox container based on selected category
$(document).on('change', '.search-category', function() {
  var row = $(this).closest('.row');
  var inputField = row.find('.search-input');
  var checkboxContainer = row.find('.checkbox-container');
  var checkboxContainer_payment = row.find('.checkbox-container-payment');

  if ($(this).val() === 'category') {
     inputField.hide();
     checkboxContainer.show();
     checkboxContainer_payment.hide();
  } else if ($(this).val() === 'payment_method') {
     inputField.hide();
     checkboxContainer.hide();
     checkboxContainer_payment.show();
  }else {
    inputField.show();
    checkboxContainer.hide();
    checkboxContainer_payment.hide();
  }
});
    // Validate Fields Function
function validateFields() {
    var rows = $('.row');
    var formData = [];
    var isValid = true;

    rows.each(function() {
        var row = $(this);
        var selectField = row.find('select');
        var inputField = row.find('input');
        var selectValue = selectField.val();
        var inputValue = inputField.val();

        var isRowValid = true;

        if (selectValue === '') {
            isRowValid = false;
            selectField.addClass('is-invalid');
            row.find('.invalid-feedback1').show();
        } else {
            selectField.removeClass('is-invalid');
            row.find('.invalid-feedback1').hide();
        }

        if (inputValue === '' && inputField.attr('type') !== 'radio') {
            isRowValid = false;
            inputField.addClass('is-invalid');
            row.find('.invalid-feedback').show();
        } else {
            inputField.removeClass('is-invalid');
            row.find('.invalid-feedback').hide();
        }

        // Handle radio buttons separately
        if (inputField.attr('type') === 'radio') {
            var radioButtons = row.find('input[type="radio"]');
            var isAnyRadioButtonChecked = radioButtons.is(':checked');
            if (!isAnyRadioButtonChecked) {
                isRowValid = false;
                row.find('.checkbox-container').addClass('is-invalid');
                row.find('.invalid-feedback').show();
            } else {
                row.find('.checkbox-container').removeClass('is-invalid');
                row.find('.invalid-feedback').hide();
            }
        }

        if (isRowValid) {
            var rowData = {
                'search_category': selectValue,
                'search_value': inputValue
            };
            formData.push(rowData);
        } else {
            isValid = false;
        }
    });

    return isValid;
}

 $('#myForm').submit(function(event) {
  event.preventDefault(); 
  $
  // validateFields();
  $('.spinner-border').show();
   formData = $(this).serializeArray();
    
  $.ajax({
    type: 'POST',
    url: '<?php echo site_url($_directory . 'controls/Advance_search_filter'); ?>',
    data: formData,
    success: function(response) {
      // console.log(response);
    $('#myDropdown').show();
    $('#dtBasicExample_wrapper').show();
    $('#dtBasicExample').show();
      try {

     if (response && Array.isArray(response.table_record)) {
          var tableBody = $('#tableBody');
          tableBody.empty(); // Clear existing table rows
          $('.spinner-border').hide();
          
          $.each(response.table_record, function(index, row) {
            var tableRow = '<tr>' +
              '<td>' + row.card_holder_name + '</td>' +
              '<td>' + row.full_name + '</td>' +
              '<td>' + row.transaction_amount + '</td>' +
              '<td>' + row.date + '</td>' +
              '<td>' + row.purpose + '</td>' +
              '<td>' + row.payment_method + '</td>' +
              '<td>' + row.donation_mode + '</td>' +
              '<td>' + row.country_name + '</td>' +
              '<td>' + row.email_address + '</td>' +
              '<td>' + row.transaction_status + '</td>';
       if (row.receipt_id && row.tax_receipt) {
           if (row.pkg_title) {
                row.receipt_purpose = 'eventregistration';
              }
              tableRow +=
                '<td><a class="btn btn-success btn-sm" href="' +
                '<?php echo site_url($_directory . "controls/receipt/") ?>/' +
                row.receipt_id +
                '/' +
                row.receipt_purpose.replace(/ /g, '').toLowerCase() +
                '/' +
                row.tax_receipt +
                '/' +
                row.date +
                '">' +
                row.receipt_prefix +
                row.tax_receipt +
                '</a></td>';
            } else {
              tableRow += '<td>N/A</td>';

            }

            tableRow += '<td>' + row.reconciliation_status + '</td>' +
              '</tr>';

            tableBody.append(tableRow);
          $('.spinner-border').hide();

          });
        } else {
          $('.spinner-border').hide();
          console.error('Invalid response format or empty table_record array.');
        }
      } catch (err) {
        console.error('Error occurred while processing the response:', err);
      $('.spinner-border').hide();


      }
    },
    error: function(xhr, textStatus, errorThrown) {
      // Handle any errors that occurred during the AJAX request
      $('.spinner-border').hide();
      console.error('AJAX Error:', errorThrown);
    }
  });

 });
 
})
  function DownloadCsv(csv_type) {
    
   var formData = $('#myForm').serializeArray();
   formData.push({ name: 'csv_type', value: csv_type });
    // console.log(formData)
      
    $.ajax({
      type: 'POST',
      url: '<?php echo site_url($_directory . 'controls/downloadfiltercsv'); ?>',
      data: formData,
      success: function(response1) {
    // console.log(response1);
         var csvData = new Blob([response1], { type: 'text/csv;charset=utf-8;' });
      var csvUrl = window.URL.createObjectURL(csvData);
      var tempLink = document.createElement('a');
      tempLink.href = csvUrl;
      tempLink.setAttribute('download', new Date().toISOString() + '-' + csv_type + '.csv');
      tempLink.click();
        // Handle the response, e.g., trigger a CSV download
      },
      error: function(xhr, textStatus, errorThrown) {
        // Handle any errors that occurred during the AJAX request
        console.error('AJAX Error:', errorThrown);
      }
    });
  }
</script>