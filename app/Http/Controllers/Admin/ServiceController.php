<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('admin.services.index', [
            'services' => Service::latest()->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.services.create', ['service' => new Service]);
    }

    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $data['image_path'] = $request->file('image')?->store('services', 'public');

        Service::create($data);

        return redirect()->route('admin.services.index')->with('status', 'Service created.');
    }

    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        $data = $request->validated();
        $expectedVersion = (int) $data['lock_version'];
        unset($data['lock_version']);
        unset($data['image']);
        $data['is_active'] = $request->boolean('is_active');
        $newImagePath = $request->file('image')?->store('services', 'public');

        if ($newImagePath !== null) {
            $data['image_path'] = $newImagePath;
        }

        $updated = Service::query()
            ->whereKey($service->id)
            ->where('lock_version', $expectedVersion)
            ->update([...$data, 'lock_version' => $expectedVersion + 1, 'updated_at' => now()]);

        if ($updated !== 1) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw ValidationException::withMessages([
                'conflict' => 'This service was changed by another staff member. Reload it and reapply your changes.',
            ]);
        }

        if ($newImagePath !== null && $service->image_path !== null) {
            Storage::disk('public')->delete($service->image_path);
        }

        return redirect()->route('admin.services.index')->with('status', 'Service updated.');
    }

    public function destroy(Service $service): RedirectResponse
    {
        if ($service->image_path !== null) {
            Storage::disk('public')->delete($service->image_path);
        }

        $service->delete();

        return redirect()->route('admin.services.index')->with('status', 'Service deleted.');
    }
}