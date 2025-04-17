<!DOCTYPE html>
<html>
<head>
    <title>Nieuwe Mergevoorstellen Aangemaakt</title>
</head>
<body>
<h1>Nieuwe Mergevoorstellen zijn aangemaakt</h1>
<p>De volgende mergevoorstellen zijn aangemaakt voor de release:</p>
<ul>
    @foreach ($pullRequestUrls as $url)
        <li><a href="{{ $url }}" target="_blank">{{ $url }}</a></li>
    @endforeach
</ul>
</body>
</html>

