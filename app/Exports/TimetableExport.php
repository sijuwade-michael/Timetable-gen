<!-- <?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TimetableExport implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data; // timetable data passed from the controller
    }

    public function view(): View
    {
        return view('exports.timetable', [
            'timetable' => $this->data
        ]);
    }
} -->
