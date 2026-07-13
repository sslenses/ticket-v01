import re

def uncompile_blade(content):
    # 1. <?php echo e($expr); ?> -> {{ $expr }}
    content = re.sub(r'<\?php echo e\((.*?)\); \?>', r'{{ \1 }}', content)
    
    # 2. <?php echo $expr; ?> -> {!! $expr !!}
    content = re.sub(r'<\?php echo (.*?); \?>', r'{!! \1 !!}', content)
    
    # 3. <?php if($cond): ?> -> @if($cond)
    content = re.sub(r'<\?php if\((.*?)\): \?>', r'@if(\1)', content)
    
    # 4. <?php elseif($cond): ?> -> @elseif($cond)
    content = re.sub(r'<\?php elseif\((.*?)\): \?>', r'@elseif(\1)', content)
    
    # 5. <?php else: ?> -> @else
    content = re.sub(r'<\?php else: \?>', r'@else', content)
    
    # 6. <?php endif; ?> -> @endif
    content = re.sub(r'<\?php endif; \?>', r'@endif', content)
    
    # 7. Foreach loops
    # <?php $__currentLoopData = $arr; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    # Wait, there's a simpler regex: just find <?php foreach(... as ...): ?> if it exists, or just look for forelse patterns.
    # Let's handle generic foreach
    content = re.sub(r'<\?php foreach\((.*?) as (.*?)\): .*?\?>', r'@foreach(\1 as \2)', content)
    content = re.sub(r'<\?php endforeach; .*?\?>', r'@endforeach', content)
    
    # 8. Forelse
    content = re.sub(r'<\?php \$__empty_1 = true; \$__currentLoopData = (.*?); \$__env->addLoop\(\$__currentLoopData\); foreach\(\$__currentLoopData as (.*?)\): .*?\$__empty_1 = false; \?>', r'@forelse(\1 as \2)', content)
    content = re.sub(r'<\?php endforeach; \$__env->popLoop\(\); \$loop = \$__env->getLastLoop\(\); if \(\$__empty_1\): \?>', r'@empty', content)
    
    # 9. @include
    content = re.sub(r'\{!! \$__env->make\(\'(.*?)\', .*?\)->render\(\) !!\}', r"@include('\1')", content)
    
    # 10. CSRF / Method
    content = re.sub(r'\{!! csrf_field\(\) !!\}', r'@csrf', content)
    content = re.sub(r'\{!! method_field\(\'(.*?)\'\) !!\}', r"@method('\1')", content)
    
    # If there are any <?php ?> without echo, convert them to @php ... @endphp
    # But usually there are specific Laravel things. Let's see what's left.
    
    return content

if __name__ == "__main__":
    with open('resources/views/ticket-detail-backup.php', 'r') as f:
        content = f.read()
    
    uncompiled = uncompile_blade(content)
    
    with open('resources/views/ticket-detail.blade.php', 'w') as f:
        f.write(uncompiled)
    
    print("Done uncompiling!")
