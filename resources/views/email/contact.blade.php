<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Email</title>
</head>
<body>
    <h2>You have received an enquiry</h2>
    <p>Name : {{ $mailData['name'] }}</p>
    <p>Email : {{ $mailData['email'] }}</p>
    <p>Phone : {{ $mailData['phone'] }}</p>
    <p>Subject : {{ $mailData['subject'] }}</p>
    <p>Message :</p>
    <p>{{ $mailData['message'] }}</p>
    <br>
    <p>Thanks</p>
</body>
</html>