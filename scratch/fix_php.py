import re
with open('resources/views/ticket-detail.blade.php', 'r') as f:
    c = f.read()

c = re.sub(r'<\?php echo json_encode\(\$tenants, 15, 512\) \?>', r'@json($tenants)', c)
c = re.sub(r'<\?php if \(app\(\\Illuminate\\Contracts\\Auth\\Access\\Gate::class\)->check\(\'update\', \$ticket\)\): \?>', r'@can(\'update\', $ticket)', c)
c = re.sub(r'<\?php if \(app\(\\Illuminate\\Contracts\\Auth\\Access\\Gate::class\)->check\(\'cancel\', \$ticket\)\): \?>', r'@can(\'cancel\', $ticket)', c)
c = re.sub(r'<\?php /\*\*PATH .*?\*\*/ \?>\n?', r'', c)

with open('resources/views/ticket-detail.blade.php', 'w') as f:
    f.write(c)
