// 1. Open Google Sheets and create a new blank sheet.
// 2. Name the sheet "Orders".
// 3. In the first row, create the following headers in cells A1 to I1:
//    OrderNumber, Date, Name, Address, Phone, Product, ShippingLocation, ShippingCost, TotalPrice
// 4. Go to Extensions > Apps Script.
// 5. Delete any existing code in the script editor and paste the code below.
// 6. Click the "Deploy" button, select "New deployment".
// 7. For "Select type", click the gear icon and choose "Web app".
// 8. In the "Description" field, you can write "Order Form Handler".
// 9. For "Who has access", select "Anyone". This is important.
// 10. Click "Deploy".
// 11. Click "Authorize access" and follow the prompts to authorize the script with your Google account.
// 12. After deploying, copy the "Web app URL". This is the URL you will paste into the 'googleScriptURL' variable in your index.html file.

function doPost(e) {
  try {
    var sheet = SpreadsheetApp.getActiveSpreadsheet().getSheetByName("Orders");
    var data = JSON.parse(e.postData.contents);
    
    // Convert date from ISO string to a readable format for the sheet
    var formattedDate = new Date(data.date).toLocaleString("en-US", {timeZone: "Asia/Dhaka"});

    sheet.appendRow([
      data.orderNumber,
      formattedDate,
      data.name,
      data.address,
      data.phone,
      data.product,
      data.shipping_location,
      data.shipping_cost,
      data.total_price
    ]);
    
    return ContentService.createTextOutput(JSON.stringify({ "result": "success" })).setMimeType(ContentService.MimeType.JSON);
    
  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({ "result": "error", "error": error.toString() })).setMimeType(ContentService.MimeType.JSON);
  }
}
