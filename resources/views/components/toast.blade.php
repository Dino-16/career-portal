@if(session('status'))
    <div
        aria-live="polite"
        aria-atomic="true"
        @class('position-relative')
    >
        {{-- Toast container in the top right --}}
        <div
            @class('toast-container position-fixed top-0 end-0 p-3')
            style="z-index: 2000;"
            wire:poll.4s="clearStatus"
        >
            @foreach((array) session('status') as $message)
                <div
                    @class('toast show align-items-center text-bg-success border-0')
                    role="alert"
                    aria-live="assertive"
                    aria-atomic="true"
                >
                    <div @class('d-flex')>
                        <div @class('toast-body px-3')>
                            <i @class('bi bi-check-circle-fill me-2')></i>{{ $message }}
                        </div>

                        <button
                            type="button"
                            @class('btn-close btn-close-white me-2 m-auto')
                            data-bs-dismiss="toast"
                            aria-label="Close"
                            wire:click="clearStatus"
                        ></button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif