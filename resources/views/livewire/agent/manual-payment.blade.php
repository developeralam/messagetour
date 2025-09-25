<div class="min-h-screen bg-gradient-to-br from-gray-50 to-white">
    <!-- Header -->
    <div class="bg-white shadow-lg border-b border-gray-200">
        <div class="px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Manual Payment</h1>
                    <p class="text-gray-600 text-sm">Submit your payment request with bank details</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center">
                    <x-fas-credit-card class="w-6 h-6 text-white" />
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <x-fas-check-circle class="w-5 h-5 mr-2" />
                    {{ session('success') }}
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                <div class="flex items-center">
                    <x-fas-exclamation-circle class="w-5 h-5 mr-2" />
                    {{ session('error') }}
                </div>
            </div>
        @endif

        <!-- Payment Form -->
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-xl border border-gray-200 shadow-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-900">Payment Details</h2>
                    <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <x-fas-money-bill-wave class="w-4 h-4 text-white" />
                    </div>
                </div>

                <form wire:submit.prevent="submitPayment" class="space-y-6">
                    <!-- Bank Account Selection -->
                    <div>
                        <label for="account_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Select Bank Account <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="account_id" id="account_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors">
                            <option value="">Choose a bank account...</option>
                            @foreach ($bank_accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('account_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Amount -->
                    <div>
                        <label for="amount" class="block text-sm font-medium text-gray-700 mb-2">
                            Amount (৳) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" wire:model="amount" id="amount" step="0.01" min="1"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Enter amount">
                        @error('amount')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Reference -->
                    <div>
                        <label for="reference" class="block text-sm font-medium text-gray-700 mb-2">
                            Reference/Transaction ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="reference" id="reference"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Enter transaction reference or ID">
                        @error('reference')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">
                            Remarks (Optional)
                        </label>
                        <textarea wire:model="remarks" id="remarks" rows="3"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-colors"
                            placeholder="Add any additional remarks..."></textarea>
                        @error('remarks')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Payment Slip Upload -->
                    <div>
                        <label for="payment_slip" class="block text-sm font-medium text-gray-700 mb-2">
                            Payment Slip/Receipt <span class="text-red-500">*</span>
                        </label>
                        <div
                            class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-green-400 transition-colors">
                            <div class="space-y-1 text-center">
                                @if ($payment_slip)
                                    <div class="flex items-center justify-center">
                                        <img src="{{ $payment_slip->temporaryUrl() }}" alt="Payment slip preview"
                                            class="h-32 w-auto rounded-lg border border-gray-300">
                                    </div>
                                    <p class="text-sm text-green-600 font-medium">{{ $payment_slip->getClientOriginalName() }}</p>
                                @else
                                    <x-fas-cloud-upload-alt class="mx-auto h-12 w-12 text-gray-400" />
                                    <div class="flex text-sm text-gray-600">
                                        <label for="payment_slip"
                                            class="relative cursor-pointer bg-white rounded-md font-medium text-green-600 hover:text-green-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-green-500">
                                            <span>Upload a file</span>
                                            <input id="payment_slip" wire:model="payment_slip" type="file" class="sr-only" accept="image/*">
                                        </label>
                                        <p class="pl-1">or drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, JPEG up to 2MB</p>
                                @endif
                            </div>
                        </div>
                        @error('payment_slip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full bg-gradient-to-r from-green-500 to-emerald-600 text-white font-semibold py-3 px-6 rounded-lg hover:from-green-600 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-300 transform hover:scale-105">
                            <div class="flex items-center justify-center">
                                <x-fas-paper-plane class="w-5 h-5 mr-2" />
                                Submit Payment Request
                            </div>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Information Card -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start">
                    <x-fas-info-circle class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" />
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">Important Information:</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Your payment request will be reviewed by admin before approval</li>
                            <li>Once approved, the amount will be added to your account</li>
                            <li>Please ensure the payment slip is clear and readable</li>
                            <li>Reference/Transaction ID should match your bank statement</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
