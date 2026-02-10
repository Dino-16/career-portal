@props(['message' => session('success'), 'poll' => true])

<div>
    @if($message)
        <div
            @if($poll) wire:poll.1000ms.remove @endif
            {{ $attributes->merge(['class' => 'alert alert-success']) }}
            role="alert"
        >
            {{ $message }}
        </div>
    @endif
</div>