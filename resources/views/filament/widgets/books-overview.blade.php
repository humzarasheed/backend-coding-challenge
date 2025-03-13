<div class="col-span-full w-full p-2 bg-white rounded-xl shadow">
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 p-4">
        @foreach($books as $book)
            <div class="bg-white border rounded-xl shadow overflow-hidden hover:shadow-md transition-shadow w-full">
                <div class="flex items-center justify-center h-40 bg-gray-100">
                    <svg class="w-20 h-20 text-gray-400" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                    </svg>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-semibold">{{ $book->name }}</h3>
                    <p class="text-gray-600">By: {{ $book->author->name ?? 'Unknown Author' }}</p>

                    <div class="mt-3 flex justify-between">
                        <span class="text-sm {{ $book->is_published ? 'text-green-600' : 'text-orange-600' }}">
                            {{ $book->is_published ? 'Published' : 'Draft' }}
                        </span>
                        <a href="{{ $this->getManageBookUrl($book) }}" class="text-primary-600 hover:underline text-sm">
                            Manage Book →
                        </a>
                    </div>
                </div>
            </div>
        @endforeach

        @if($books->isEmpty())
            <div class="col-span-full flex justify-center items-center p-6">
                <p class="text-gray-500">No books found. Create your first book!</p>
            </div>
        @endif
    </div>

    {{-- <div class="p-4 flex justify-end">
        <a href="{{ $this->getCreateBookUrl() }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
            Add New Book
        </a>
    </div> --}}
</div>
