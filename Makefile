PLUGIN_FILES = $(shell git ls-files)

TARGET = advanced-admin-search.zip

$(TARGET): $(PLUGIN_FILES)
	zip $(TARGET) $(PLUGIN_FILES)

clean:
	-$(RM) -r assets/*/node_modules
	-$(RM) *.zip

.PHONY: clean
