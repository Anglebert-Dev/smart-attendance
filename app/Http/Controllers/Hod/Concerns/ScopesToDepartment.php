<?php

namespace App\Http\Controllers\Hod\Concerns;

use App\Models\SchoolClass;
use App\Support\Department;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToDepartment
{
    protected function hodDepartmentCode(): string
    {
        $code = Department::normalize(auth()->user()->department);

        if (!$code || !array_key_exists($code, Department::OPTIONS)) {
            abort(403, 'Your account is not assigned to a department.');
        }

        return $code;
    }

    protected function departmentClassesQuery(): Builder
    {
        return SchoolClass::forDepartment($this->hodDepartmentCode());
    }

    protected function departmentClassIds(): array
    {
        return $this->departmentClassesQuery()->pluck('id')->all();
    }

    protected function ensureClassInDepartment(SchoolClass $class): void
    {
        if (Department::normalize($class->department) !== $this->hodDepartmentCode()) {
            abort(403, 'You do not have access to this class.');
        }
    }
}
