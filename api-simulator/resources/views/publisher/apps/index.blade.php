@extends('layouts.publisher')

@section('title', 'Apps')

@section('content')
@if(session('success'))
    <div class="mb-4 p-3 rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 text-sm">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="mb-4 p-3 rounded-xl border border-red-300 bg-red-50 text-red-700 text-sm">
        <ul class="space-y-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Apps</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your applications and monitor their monetization performance.</p>
    </div>
    <button type="button" onclick="document.getElementById('addAppModal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm transition-colors">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none"><path d="M12 4v16m8-8H4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        Add App
    </button>
</div>

<div class="bg-white rounded-xl border border-gray-200 mb-6">
    <div class="p-4 border-b border-gray-100">
        <form method="GET" action="{{ route('publisher.apps') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1 max-w-md">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" viewBox="0 0 24 24" fill="none"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by app name, URL, category..." class="pl-10 pr-4 py-2 w-full rounded-lg border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500">
            </div>
            <button type="submit" class="px-4 py-2 rounded-lg bg-gray-100 text-sm font-medium text-gray-600 hover:bg-gray-200 transition-colors">Search</button>
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-t border-gray-100 bg-gray-50/50">
                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">ID</th>
                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Application Type</th>
                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Application URL</th>
                    <th class="px-4 py-3 text-left text-[10px] font-semibold uppercase tracking-wider text-gray-400">Application Name</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Impression</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Clicks</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">CTR(%)</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">ECPM</th>
                    <th class="px-4 py-3 text-right text-[10px] font-semibold uppercase tracking-wider text-gray-400">Revenue</th>
                    <th class="px-4 py-3 text-center text-[10px] font-semibold uppercase tracking-wider text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($apps as $app)
                    @php $row = $stats[$app->id] ?? null; @endphp
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-4 py-3 font-mono text-xs text-gray-500">#{{ $app->id }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $applicationTypes[$app->application_type ?? 'web'] ?? ucfirst((string) ($app->application_type ?? 'web')) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                {{ ucfirst(str_replace('_', ' ', $app->status)) }}
                            </span>
                        </td>
                        <td class="px-4 py-3"><a href="{{ $app->app_url }}" target="_blank" class="text-sm text-brand-600 hover:text-brand-700 truncate max-w-[220px] inline-block">{{ $app->app_url }}</a></td>
                        <td class="px-4 py-3"><div class="font-medium text-gray-900">{{ $app->app_name }}</div><div class="text-xs text-gray-400">{{ $categories[$app->category] ?? ucfirst((string) $app->category) }}</div></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format((int) ($row->impressions ?? 0)) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format((int) ($row->clicks ?? 0)) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format((float) ($row->ctr ?? 0), 2) }}%</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">&euro;{{ number_format((float) ($row->ecpm ?? 0), 2) }}</td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900">&euro;{{ number_format((float) ($row->revenue ?? 0), 2) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-1">
                                <button onclick="editApp({{ $app->id }})" class="p-1.5 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors" title="Edit">Edit</button>
                                <button onclick="deleteApp({{ $app->id }})" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors" title="Delete">Delete</button>
                                <button onclick="openAdblockWizard({{ $app->id }}, @js($app->app_name))" class="p-1.5 rounded-lg hover:bg-emerald-50 text-gray-400 hover:text-emerald-600 transition-colors" title="Adblock">Adblock</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-12 text-center text-sm text-gray-500">No applications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($apps->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $apps->links() }}</div>@endif
</div>

<div id="addAppModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-900">Add App</h3><button onclick="document.getElementById('addAppModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">x</button></div>
        <form method="POST" action="{{ route('publisher.apps.store') }}" class="p-6 space-y-4">
            @csrf
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application Type</label><select name="application_type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">@foreach($applicationTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application URL</label><input type="text" name="app_url" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application Name</label><input type="text" name="app_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label><select name="category" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">@foreach($categories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="document.getElementById('addAppModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button><button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Create App</button></div>
        </form>
    </div>
</div>

<div id="editAppModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm" onclick="if(event.target===this) this.classList.add('hidden')">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100"><h3 class="text-lg font-bold text-gray-900">Edit App</h3><button onclick="document.getElementById('editAppModal').classList.add('hidden')" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">x</button></div>
        <form id="editAppForm" method="POST" class="p-6 space-y-4">
            @csrf @method('PUT')
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application Type</label><select id="edit_application_type" name="application_type" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">@foreach($applicationTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application URL</label><input type="text" id="edit_app_url" name="app_url" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Application Name</label><input type="text" id="edit_app_name" name="app_name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
            <div><label class="block text-xs font-medium text-gray-600 mb-1">Category</label><select id="edit_category" name="category" required class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm">@foreach($categories as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="document.getElementById('editAppModal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button><button type="submit" class="px-5 py-2 rounded-lg bg-brand-600 text-sm font-semibold text-white hover:bg-brand-700 shadow-sm">Save Changes</button></div>
        </form>
    </div>
</div>

<div id="adblockWizardModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" onclick="if(event.target===this) closeAdblockWizard()">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[92vh] overflow-hidden flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100"><div><h3 class="text-lg font-bold text-gray-900">Create App Adblock</h3><p id="selectedAppLabel" class="text-xs text-gray-500 mt-1"></p></div><button onclick="closeAdblockWizard()" class="p-1.5 rounded-lg hover:bg-gray-100 text-gray-400">x</button></div>
        <div class="px-6 py-3 border-b border-gray-100"><div id="adblockWizardError" class="hidden rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700"></div></div>
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Application</label><input id="selectedAppInput" type="text" disabled class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Ad Type</label><select id="adTypeSelect" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"><option value="display_web">Display Web</option><option value="special_web">Special Web</option><option value="display_video">Display Video</option></select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Size</label><select id="adFormatSelect" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></select></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Adblock Name</label><input id="adblock_name" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Floor Price</label><input id="floor_price" type="text" value="0.00" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Background Color</label><input id="bg_color" type="color" value="#ffffff" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-2 py-1"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Sponsored Prefix</label><input id="sponsored_prefix" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Native Image Width</label><input id="image_width" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Native Image Height</label><input id="image_height" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div class="md:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Pass Back Tags</label><textarea id="passback" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></textarea></div>
                <div class="md:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">HTML Template</label><textarea id="html_template" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></textarea></div>
                <div class="md:col-span-2"><label class="block text-xs font-medium text-gray-600 mb-1">Custom CSS</label><textarea id="custom_css" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></textarea></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">CSS Path</label><input id="css_path" type="text" class="w-full px-3 py-2 rounded-lg border border-gray-200 text-sm"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Inline Video</label><button type="button" id="inline_video" data-enabled="0" onclick="toggleInlineVideo()" class="inline-flex h-10 min-w-24 items-center justify-center rounded-lg border border-gray-200 px-4 text-sm font-medium text-gray-600 hover:bg-gray-50">OFF</button></div>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach(['js' => 'JS', 'iframe' => 'IFRAME', 'inline' => 'Inline', 'real' => 'Video Real', 'small' => 'Video Small', 'box' => 'Video Box', 'head' => 'Video Head', 'overlay' => 'Overlay', 'curl' => 'PHP'] as $tabId => $tabLabel)
                    <button type="button" id="tab-{{ $tabId }}" onclick="switchCodeTab('{{ $tabId }}')" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50">{{ $tabLabel }}</button>
                @endforeach
            </div>
            @foreach(['js', 'iframe', 'inline', 'real', 'small', 'box', 'head', 'overlay', 'curl'] as $tabId)
                <textarea id="{{ $tabId }}" rows="8" class="hidden w-full rounded-xl border border-gray-200 bg-gray-950 px-4 py-3 font-mono text-xs text-emerald-200 focus:outline-none"></textarea>
            @endforeach
        </div>
        <div class="flex items-center justify-end gap-3 border-t border-gray-100 px-6 py-4">
            <button type="button" onclick="closeAdblockWizard()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Close</button>
            <button type="button" onclick="submitAdblockWizard()" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Generate Code</button>
            <button type="button" onclick="copyActiveCodeTab()" class="rounded-lg border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50">Copy Code</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const publisherAppAdFormats = {
    display_web: { sizes: { '300x250': '300x250 (Medium Rectangle)', '728x90': '728x90 (Leaderboard)', '160x600': '160x600 (Wide Skyscraper)', '300x600': '300x600 (Half Page)', '320x50': '320x50 (Mobile Banner)', '970x250': '970x250 (Billboard)' } },
    special_web: { sizes: { native: 'Native Ad', interstitial: 'Interstitial', popunder: 'Popunder', direct_link: 'Direct Link', in_page_push: 'In-Page Push', social_bar: 'Social Bar', text: 'Text Ad' } },
    display_video: { sizes: { instream: 'In-Stream Video', outstream: 'Out-Stream Video', rewarded: 'Rewarded Video' } }
};
let selectedAppId = null;
let activeCodeTab = 'js';
function editApp(id){fetch('/publisher/apps/'+id).then(r=>r.json()).then(data=>{document.getElementById('edit_application_type').value=data.application_type||'web';document.getElementById('edit_app_url').value=data.app_url||'';document.getElementById('edit_app_name').value=data.app_name||'';document.getElementById('edit_category').value=data.category||'custom';document.getElementById('editAppForm').action='/publisher/apps/'+id;document.getElementById('editAppModal').classList.remove('hidden');});}
function deleteApp(id){if(!confirm('Delete this application?'))return;fetch('/publisher/apps/'+id,{method:'DELETE',headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}}).then(()=>window.location.reload());}
function openAdblockWizard(appId,appName){selectedAppId=appId;document.getElementById('selectedAppLabel').textContent=appName;document.getElementById('selectedAppInput').value=appName;document.getElementById('adblock_name').value=appName+' Adblock';document.getElementById('floor_price').value='0.00';document.getElementById('passback').value='';document.getElementById('html_template').value='';document.getElementById('custom_css').value='';document.getElementById('image_width').value='';document.getElementById('image_height').value='';document.getElementById('sponsored_prefix').value='';document.getElementById('css_path').value='';document.getElementById('bg_color').value='#ffffff';document.getElementById('inline_video').dataset.enabled='0';document.getElementById('inline_video').textContent='OFF';document.getElementById('adblockWizardError').classList.add('hidden');populateAdFormats();switchCodeTab('js');document.getElementById('adblockWizardModal').classList.remove('hidden');}
function closeAdblockWizard(){document.getElementById('adblockWizardModal').classList.add('hidden');}
function populateAdFormats(){const adType=document.getElementById('adTypeSelect').value;const formatSelect=document.getElementById('adFormatSelect');formatSelect.innerHTML='';Object.entries(publisherAppAdFormats[adType].sizes).forEach(([value,label])=>{const option=document.createElement('option');option.value=value;option.textContent=label;formatSelect.appendChild(option);});}
function resolvedZoneType(rawFormat,adType){if(adType==='display_video'||['instream','outstream','rewarded'].includes(rawFormat))return'video';if(rawFormat==='native')return'native';if(rawFormat==='popunder')return'popup';if(rawFormat==='interstitial')return'interstitial';if(rawFormat==='social_bar')return'bannerbox';if(rawFormat==='in_page_push')return'overlay';return'banner';}
function toggleInlineVideo(){const button=document.getElementById('inline_video');const enabled=button.dataset.enabled==='1';button.dataset.enabled=enabled?'0':'1';button.textContent=enabled?'OFF':'ON';}
function showWizardError(message){const box=document.getElementById('adblockWizardError');box.textContent=message;box.classList.remove('hidden');}
function switchCodeTab(tabId){activeCodeTab=tabId;['js','iframe','inline','real','small','box','head','overlay','curl'].forEach(id=>{document.getElementById(id).classList.toggle('hidden',id!==tabId);document.getElementById('tab-'+id).className='rounded-lg border px-3 py-1.5 text-xs font-semibold '+(id===tabId?'border-brand-600 bg-brand-600 text-white':'border-gray-200 text-gray-600 hover:bg-gray-50');});}
function copyActiveCodeTab(){const textarea=document.getElementById(activeCodeTab);textarea.select();document.execCommand('copy');}
function submitAdblockWizard(){if(!selectedAppId)return showWizardError('Please choose an application first.');const name=document.getElementById('adblock_name').value.trim();if(!name)return showWizardError('Please enter an adblock name.');const formatId=document.getElementById('adTypeSelect').value;const sizeId=document.getElementById('adFormatSelect').value;const zoneType=resolvedZoneType(sizeId,formatId);const placement=['overlay','popup','interstitial'].includes(zoneType)?zoneType:'content';fetch('/publisher/apps/'+selectedAppId+'/adblocks',{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({name:name,format_id:formatId,size_id:sizeId,zone_type:zoneType,placement:placement,floor_price:document.getElementById('floor_price').value||0,passback:document.getElementById('passback').value||null,image_width:document.getElementById('image_width').value||null,image_height:document.getElementById('image_height').value||null,html_template:document.getElementById('html_template').value||null,custom_css:document.getElementById('custom_css').value||null,bg_color:document.getElementById('bg_color').value||null,sponsored_prefix:document.getElementById('sponsored_prefix').value||null,css_path:document.getElementById('css_path').value||null,inline_video:document.getElementById('inline_video').dataset.enabled==='1'})}).then(async response=>{const data=await response.json();if(!response.ok)throw new Error(data.message||'Unable to create adblock.');return data;}).then(data=>{Object.entries(data.codes||{}).forEach(([id,value])=>{if(document.getElementById(id))document.getElementById(id).value=value||'';});switchCodeTab('js');}).catch(error=>showWizardError(error.message));}
document.getElementById('adTypeSelect').addEventListener('change',populateAdFormats);populateAdFormats();
</script>
@endpush
