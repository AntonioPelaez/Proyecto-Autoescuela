 public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'name' => 'required|string|max:80',
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'name' => $validated['name'],
            'role_id' => 3, // alumno
        ]);

        StudentProfile::create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Alumno registrado correctamente',
            'user' => $user,
        ], 201);
    }