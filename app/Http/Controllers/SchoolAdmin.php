namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {

        return Inertia::render('SchoolAdmin/Dashboard');
    }
}

