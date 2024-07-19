<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Technology;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function index(Request $request)
    {
            $technologies = Technology::all()->toArray();
            return view('dashboard', compact('technologies'));
    }


    public function viewClientDetails(Request $request)
    {
        $request->validate([
            'searchTerm' => 'nullable|string|max:255',
            'rowsPerPage' => 'nullable|integer|min:1'
        ]);

        try {
            $user = Auth::user();
            $query = Client::with('technologies', 'projects')->latest();

            if ($request->has('searchTerm')) {
                $query->search($request->searchTerm);
            }

            if (!($user->is_admin || $user->is_super_admin)) {
                $query->where('user_id', $user->id);
            }

            $clients = $query->get()->toArray();

            if (count($clients)> 0) {
                return response()->json(['success' => true, 'data' => $clients], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No records found.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'An error occurred while fetching client details: ' . $e->getMessage()], 500);
        }
    }





    public function saveClientDetails(Request $request)
    {
        DB::beginTransaction(); // Begin a transaction to ensure atomicity

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'email' => 'required|email|unique:clients,email',
                'contact' => 'required|min:10',
                'country' => 'required',
                'websiteUrl' => 'required',
                'projects' => 'required|json',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return response()->json(['message' => $validator->messages()->all()], 200);
            }

            $client = Client::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'contact' => $request->contact,
                'email' => $request->email,
                'skype_id' => $request->skypeId,
                'address' => $request->address,
                'country' => $request->country,
                'website_url' => $request->websiteUrl,
                'linkedin_url' => $request->linkedinId,
                'facebook_url' => $request->facebookId,
            ]);

            $projects = json_decode($request->projects, true);

            $projectIds = [];
            $technologyIds = [];

            foreach ($projects as $projectData) {
                $projectInfo = $projectData['projects'][0];
                $project = Project::firstOrCreate([
                    'project_title' => $projectInfo['projectTitle'],
                    'project_description' => $projectInfo['projectDescription']
                ]);

                $projectIds[] = $project->id;

                foreach ($projectData['technologies'] as $technologyData) {
                    $technology = Technology::firstOrCreate(['technology' => $technologyData['name']]);
                    $technologyIds[] = $technology->id;

                    // Attach technology to the project
                    $project->technologies()->syncWithoutDetaching($technology->id);
                }
            }

            $client->projects()->sync($projectIds);
            $client->technologies()->sync($technologyIds);

            DB::commit(); // Commit the transaction if all goes well

            return response()->json(['success' => true, 'message' => 'Clients Details Saved Successfully'], 203);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction if any error occurs
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function deleteClientDetails(Request $request)
    {
        try {
            Client::where('id', $request->clientId)->delete();
            return response()->json(['success' => true, 'message' => 'Client Details Permanently Deleted Sucessfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function showClientDetails(Request $request)
    {
        $user = Client::with('technologies', 'projects')->find($request->userId);
        $createdBy = User::find($user->user_id)->name;

        if ($user) {
            return response()->json(['success' => true, 'data' => $user , 'createdBy' => $createdBy]);
        }
        return response()->json(['success' => false, 'message' => 'User not found']);
    }

    public function editClientDetails(Request $request)
    {
        $user = Client::with('technologies', 'projects')->find($request->userId);
        if ($user) {
            return response()->json(['success' => true, 'data' => $user]);
        }
        return response()->json(['success' => false, 'message' => 'User not found']);
    }

    public function updateClientDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
            'contact' => 'required|min:10',
            'country' => 'required',
            'website_url' => 'required',
            'projects' => 'required|json',
        ]);

        if ($validator->fails()) {
            DB::rollBack();
            return response()->json(['message' => $validator->messages()->all()], 200);
        }

        $user = Client::find($request->userId);
        if ($user) {
            // Update user details
            $user->update([
                'name' => $request->name,
                'contact' => $request->contact,
                'updated_by' => Auth::user()->name,
                'email' => $request->email,
                'skype_id' => $request->skype_id ?? null,
                'address' => $request->address,
                'country' => $request->country,
                'website_url' => $request->website_url,
                'linkedin_url' => $request->linkedin_url ?? null,
                'facebook_url' => $request->facebook_url ?? null,
            ]);

            // Decode the JSON projects data
            $projects = json_decode($request->projects, true);

            // Arrays to store project and technology IDs
            $projectIds = [];
            $technologyIds = [];

            // Process each project
            foreach ($projects as $projectData) {
                $projectInfo = $projectData['projects'][0];

                // Check if the project ID is already processed
                if (in_array($projectInfo['id'], $projectIds)) {
                    // Create a new project since the ID is already processed
                    $project = Project::create([
                        'project_title' => $projectInfo['projectTitle'],
                        'project_description' => $projectInfo['projectDescription']
                    ]);
                } else {
                    $project = Project::find($projectInfo['id']);

                    // Update or create project
                    if ($project) {
                        $project->update([
                            'project_title' => $projectInfo['projectTitle'],
                            'project_description' => $projectInfo['projectDescription']
                        ]);
                    } else {
                        $project = Project::create([
                            'project_title' => $projectInfo['projectTitle'],
                            'project_description' => $projectInfo['projectDescription']
                        ]);
                    }
                }

                // Store project ID
                $projectIds[] = $project->id;

                // Process technologies for the project
                $technologyIds = [];
                foreach ($projectData['technologies'] as $technologyData) {
                    $technology = Technology::whereRaw('LOWER(technology) = ?', [strtolower($technologyData['name'])])->first();
                    if ($technology) {
                        $technologyIds[] = $technology->id;
                    } else {
                        $newTechnology = Technology::create(['technology' => $technologyData['name']]);
                        $technologyIds[] = $newTechnology->id;
                    }
                }

                // Attach technologies to the project
                $project->technologies()->sync($technologyIds);
            }

            // Sync projects with the user
            $user->projects()->sync($projectIds);

            // Get all technology IDs associated with the user's projects
            $userTechnologyIds = Project::whereIn('id', $projectIds)->with('technologies')->get()->pluck('technologies.*.id')->flatten()->unique()->toArray();

            // Sync technologies with the user
            $user->technologies()->sync($userTechnologyIds);

            return response()->json(['success' => true, 'message' => 'User updated successfully'], 200);
        }

        return response()->json(['success' => false, 'message' => 'User not found'], 404);
    }


    public function getTechnologies()
    {
        $technology = Technology::all()->toArray();
        return response()->json(['success' => true, 'data' => $technology], 200);
    }
    public function archiveClient(Request $request)
    {
        try {
            $client = Client::find($request->clientId);
            $client->update([
                'is_archived' => 1,
                'archived_at' => Carbon::now()
            ]);

            return response()->json(['success' => true, 'message' => 'Client archived successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function archivedClientDetails()
    {
        $archivedClients = Client::where('is_archived', 1)->where('user_id', Auth::user()->id)->with('technologies', 'projects')->latest()->get()->toArray();
        $technologies = Technology::all()->toArray();
        return view('client.client-archived', compact(['archivedClients', 'technologies']));
    }

    public function unArchiveClientDetails(Request $request)
    {
        try {
            $client = Client::find($request->clientId);
            $client->update([
                'is_archived' => 0,
                'archived_at' => null
            ]);
            return response()->json(['success' => true, 'message' => 'Client unarchived successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getArchivedClientDetails(Request $request)
    {
        try {
            $user = Auth::user();
            if ($user->is_admin || $user->is_super_admin) {
                $archivedClients = Client::search($request->searchTerm)->where('is_archived', 1)->with('technologies', 'projects')->latest()->get()->toArray();
            } else {
                $archivedClients = Client::search($request->searchTerm)->where('is_archived', 1)->where('user_id', $user->id)->with('technologies', 'projects')->latest()->get()->toArray();;
            }

            if (count($archivedClients) > 0) {
                return response()->json(['success' => true, 'data' =>  $archivedClients], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No records found.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    function filterClientDetails(Request $request)
    {
        try {
            $user = Auth::user();
            $query = Client::query();

            if ($request->filled('technologies')) {
                $technologies = $request->technologies;
                if (is_array($technologies)) {
                    $query->whereHas('technologies', function ($q) use ($technologies) {
                        $q->whereIn('technology', $technologies);
                    });
                } else {
                    $query->search($technologies);
                }
            }

            if ($request->filled('countries')) {
                $countries = $request->countries;
                if (is_array($countries)) {
                    $query->whereIn('country', $countries);
                } else {
                    $query->where('country', $countries);
                }
            }

            if (!$user->is_admin && !$user->is_super_admin) {
                $query->where('user_id', $user->id);
            }

            $client = $query->with('technologies', 'projects')->latest()->get()->toArray();

            if (count($client) > 0) {
                return response()->json(['success' => true, 'data' =>  $client], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'No records found.'], 200);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
