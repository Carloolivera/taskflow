<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $data = [
            'totalProjects' => Project::where('user_id', $user->id)->count(),
            'activeProjects' => Project::where('user_id', $user->id)->active()->count(),
            'totalTasks' => Task::where('user_id', $user->id)->count(),
            'pendingTasks' => Task::where('user_id', $user->id)->pending()->count(),
            'inProgressTasks' => Task::where('user_id', $user->id)->inProgress()->count(),
            'completedTasks' => Task::where('user_id', $user->id)->completed()->count(),
            'overdueTasks' => Task::where('user_id', $user->id)->overdue()->count(),
            'recentTasks' => Task::where('user_id', $user->id)
                ->with(['project', 'tags'])
                ->latest()
                ->take(5)
                ->get(),
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $data['totalUsers'] = User::count();
            $data['totalTags'] = Tag::count();
        }

        return view('livewire.dashboard', $data);
    }
}
