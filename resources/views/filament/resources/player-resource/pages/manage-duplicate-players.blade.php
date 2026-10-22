<x-filament-panels::page>
    <div class="space-y-6">
        @if(empty($duplicateGroups))
            <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 text-center text-gray-500 dark:text-gray-400">
                <div class="w-12 h-12 mx-auto text-success-500 mb-2 flex items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-lg font-medium">No duplicates found</h3>
                <p>The player database is completely clean!</p>
            </div>
        @else
            @foreach($duplicateGroups as $groupIndex => $group)
                <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                    <h3 class="text-lg font-bold mb-4">
                        Duplicate Group ({{ count($group) }} players) — Born: {{ $group[0]['birth_date'] ?? 'unknown' }}
                    </h3>
                    
                    <div class="space-y-3 mb-6">
                        @foreach($group as $player)
                            <label class="flex items-start space-x-3 p-4 border rounded-lg cursor-pointer transition-colors duration-200 border-gray-200 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5">
                                <div class="flex-shrink-0 pt-1">
                                    <input type="radio" 
                                           name="primary_{{ $groupIndex }}" 
                                           value="{{ $player['id'] ?? 0 }}" 
                                           wire:model="selectedPrimaryIds.{{ $groupIndex }}"
                                           class="w-5 h-5 text-primary-600 border-gray-300 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700">
                                </div>
                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900 dark:text-white text-base">
                                        {{ $player['first_name'] ?? '' }} {{ $player['last_name'] ?? '' }}
                                        @if(!empty($player['registration_id']))
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 ml-2">
                                                {{ $player['registration_id'] }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex gap-4">
                                        <span>ID: {{ $player['id'] ?? 0 }}</span>
                                        <span>Created: {{ $player['created_at'] ?? '' }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Massive sticky button at the bottom -->
            <div class="sticky bottom-4 z-50 p-4 bg-white dark:bg-gray-900 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 rounded-xl flex items-center justify-between mt-8">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Ready to merge all selected?</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">All unselected duplicate profiles will be permanently merged into the selected primary profiles.</p>
                </div>
                <button type="button" 
                        wire:click="mergeAll" 
                        style="background-color: #ef4444; color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; font-weight: 600; font-size: 1.1rem; box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.4);"
                        onmouseover="this.style.backgroundColor='#dc2626'"
                        onmouseout="this.style.backgroundColor='#ef4444'">
                    Merge All {{ count($duplicateGroups) }} Groups
                </button>
            </div>
        @endif
    </div>
</x-filament-panels::page>
